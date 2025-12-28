<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\RequestStatusHistory;
use App\Models\Notification;
use App\Models\User;
use App\Models\RequestUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\FCMService;

class BloodRequestController extends Controller
{
    /* =====================================================
     | 🩸 إنشاء طلب دم (من المستخدم)
     ===================================================== */
    public function store(Request $request)
    {
        $request->validate([
            'hospital_id'     => 'required|exists:users,id',
            'blood_type'      => 'required|string',
            'units_requested' => 'required|integer|min:1',
            'priority'        => 'required|in:normal,urgent',
            'notes'           => 'nullable|string',
        ]);

        $bloodRequest = BloodRequest::create([
            'requester_id'    => Auth::id(),
            'hospital_id'     => $request->hospital_id,
            'blood_type'      => $request->blood_type,
            'units_requested' => $request->units_requested,
            'priority'        => $request->priority,
            'notes'           => $request->notes,
            'status'          => 'pending',
        ]);

        $this->logStatus($bloodRequest, null, 'pending', Auth::id());

        $this->notifyUser(
            Auth::user(),
            'تم إرسال طلب الدم 🩸',
            'تم استلام طلبك بنجاح وسيتم مراجعته من قبل المستشفى.',
            $bloodRequest
        );

        return response()->json([
            'success' => true,
            'data'    => $bloodRequest
        ], 201);
    }

    /* =====================================================
     | 🔔 تغيير حالة الطلب (من المستشفى)
     ===================================================== */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,completed',
        ]);

        $bloodRequest = BloodRequest::findOrFail($id);

        if ((int)$bloodRequest->hospital_id !== (int)Auth::id()) {
            return response()->json(['success' => false], 403);
        }

        $this->changeStatusInternal($bloodRequest, $request->status, Auth::id());

        return response()->json(['success' => true]);
    }

    /* =====================================================
     | 🧠 منطق تغيير الحالة
     ===================================================== */
    private function changeStatusInternal(BloodRequest $bloodRequest, string $newStatus, int $changedBy)
    {
        if ($bloodRequest->status === $newStatus) return;

        $oldStatus = $bloodRequest->status;
        $bloodRequest->update(['status' => $newStatus]);

        $this->logStatus($bloodRequest, $oldStatus, $newStatus, $changedBy);

        // إشعار صاحب الطلب فقط
        if ($requester = User::find($bloodRequest->requester_id)) {
            $this->notifyUser(
                $requester,
                'تم تحديث حالة طلب الدم 🩸',
                'تم تحديث حالة طلبك، يرجى متابعة الإشعارات.',
                $bloodRequest
            );
        }

        // عند الموافقة → إشعار المتبرعين
        if ($newStatus === 'approved') {
            $this->notifyEligibleDonors($bloodRequest);
        }
    }

    /* =====================================================
     | 🧑‍🦰 إشعار المتبرعين (بدون مقدم الطلب)
     ===================================================== */
    private function notifyEligibleDonors(BloodRequest $request)
    {
        $hospital = User::findOrFail($request->hospital_id);

        $donors = User::eligibleDonors()
            ->where('blood_type', $request->blood_type)
            ->where('city', $hospital->city)
            ->where('id', '!=', $request->requester_id) // ❌ استبعاد مقدم الطلب
            ->get();

        foreach ($donors as $donor) {

            // منع التكرار
            if (RequestUser::where('blood_request_id', $request->id)
                ->where('user_id', $donor->id)
                ->exists()) {
                continue;
            }

            RequestUser::create([
                'blood_request_id' => $request->id,
                'user_id'          => $donor->id,
                'role_in_request'  => 'donor',
                'status'           => 'pending',
            ]);

            $body = "مستشفى {$hospital->name} يطلب دم لفصيلة {$request->blood_type} في مدينتك. هل تستطيع التبرع؟";

            Notification::create([
                'user_id'    => $donor->id,
                'title'      => '🩸 يوجد طلب تبرع بالدم',
                'body'       => $body,
                'type'       => 'blood_request_donor_alert',
                'is_read'    => false,
                'request_id' => $request->id,
            ]);

            if ($donor->fcm_token) {
                FCMService::send(
                    $donor->fcm_token,
                    '🩸 طلب تبرع بالدم',
                    $body,
                    [
                        'type'       => 'donor_alert',
                        'request_id' => (string)$request->id,
                    ]
                );
            }
        }
    }

    /* =====================================================
     | ✅❌ رد المتبرع
     ===================================================== */
    public function respondToRequest(Request $request, $id)
    {
        $request->validate([
            'response' => 'required|in:accepted,unavailable',
        ]);

        if ((int)Auth::user()->role_id !== 3) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتنفيذ هذا الإجراء'
            ], 403);
        }

        return DB::transaction(function () use ($request, $id) {

            $bloodRequest = BloodRequest::lockForUpdate()->find($id);

            if (!$bloodRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الطلب لم يعد متاحًا'
                ], 404);
            }

            if ($bloodRequest->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'تمت الموافقة من قبل متبرع آخر، شكرًا لك 🌸'
                ], 409);
            }

            if ($bloodRequest->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الطلب غير متاح حالياً'
                ], 409);
            }

            $pivot = RequestUser::where('blood_request_id', $id)
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->first();

            if (!$pivot) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم توجيه هذا الطلب إليك'
                ], 409);
            }

            if ($pivot->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'تم تسجيل ردك مسبقًا ✔'
                ], 409);
            }

            // ✔ قبول
            if ($request->response === 'accepted') {

                $pivot->update([
                    'status'       => 'accepted',
                    'responded_at' => now(),
                ]);

                $bloodRequest->update(['status' => 'completed']);

                RequestUser::where('blood_request_id', $id)
                    ->where('user_id', '!=', Auth::id())
                    ->where('status', 'pending')
                    ->update(['status' => 'unavailable']);

                $donor    = Auth::user();
                $hospital = User::find($bloodRequest->hospital_id);

                Notification::create([
                    'user_id'    => $hospital->id,
                    'title'      => 'متبرع وافق على طلب الدم 🩸',
                    'body'       => "المتبرع: {$donor->full_name}\nرقم الهاتف: {$donor->phone}",
                    'type'       => 'donor_accepted',
                    'is_read'    => false,
                    'request_id' => $bloodRequest->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'تم قبولك كمتبرع ❤️ سيتم التواصل معك قريبًا.'
                ]);
            }

            // ❌ غير متاح
            $pivot->update([
                'status'       => 'unavailable',
                'responded_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'شكرًا لك، تم تسجيل عدم توفرك 🌷'
            ]);
        });
    }

    /* =====================================================
     | 📝 تسجيل تغيير الحالة
     ===================================================== */
    private function logStatus(BloodRequest $request, $old, $new, $by)
    {
        RequestStatusHistory::create([
            'request_id' => $request->id,
            'old_status' => $old,
            'new_status' => $new,
            'changed_by' => $by,
            'changed_at' => now(),
        ]);
    }

    /* =====================================================
     | 🔔 إشعار مستخدم
     ===================================================== */
    private function notifyUser(User $user, string $title, string $body, ?BloodRequest $request = null)
    {
        Notification::create([
            'user_id'    => $user->id,
            'title'      => $title,
            'body'       => $body,
            'type'       => 'blood_request',
            'is_read'    => false,
            'request_id' => optional($request)->id,
        ]);
    }
}
