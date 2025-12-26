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
     |  🩸 إنشاء طلب دم (من المستخدم)
     ===================================================== */
    public function store(Request $request)
    {
        $request->validate([
            'hospital_id' => 'required|exists:users,id',
            'blood_type' => 'required|string',
            'units_requested' => 'required|integer|min:1',
            'priority' => 'required|in:normal,urgent',
            'notes' => 'nullable|string',
        ]);

        $bloodRequest = BloodRequest::create([
            'requester_id' => Auth::id(),
            'hospital_id' => $request->hospital_id,
            'blood_type' => $request->blood_type,
            'units_requested' => $request->units_requested,
            'priority' => $request->priority,
            'notes' => $request->notes,
            'status' => 'pending',
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
            'data' => $bloodRequest
        ], 201);
    }

    /* =====================================================
     |  🔔 تغيير حالة الطلب (من المستشفى)
     ===================================================== */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,completed',
        ]);

        $bloodRequest = BloodRequest::findOrFail($id);

        if ((int) $bloodRequest->hospital_id !== (int) Auth::id()) {
            return response()->json(['success' => false], 403);
        }

        $this->changeStatusInternal($bloodRequest, $request->status, Auth::id());

        return response()->json(['success' => true]);
    }

    /* =====================================================
     |  🧠 منطق تغيير الحالة
     ===================================================== */
    private function changeStatusInternal(BloodRequest $bloodRequest, string $newStatus, int $changedBy)
    {
        if ($bloodRequest->status === $newStatus) {
            return;
        }

        $oldStatus = $bloodRequest->status;
        $bloodRequest->update(['status' => $newStatus]);

        $this->logStatus($bloodRequest, $oldStatus, $newStatus, $changedBy);

        // إشعار صاحب الطلب
        $requester = User::find($bloodRequest->requester_id);
        if ($requester) {
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
     |  🧑‍🦰 إشعار المتبرعين + إنشاء RequestUser
     ===================================================== */
    private function notifyEligibleDonors(BloodRequest $request)
    {
        $hospital = User::findOrFail($request->hospital_id);
        $city = $hospital->city;

        $donors = User::eligibleDonors()
            ->where('blood_type', $request->blood_type)
            ->where('city', $city)
            ->get();

        foreach ($donors as $donor) {

            $exists = RequestUser::where('blood_request_id', $request->id)
                ->where('user_id', $donor->id)
                ->exists();

            if ($exists) {
                continue;
            }

            RequestUser::create([
                'blood_request_id' => $request->id,
                'user_id' => $donor->id,
                'role_in_request' => 'donor',
                'status' => 'pending',
            ]);

            $body = "مستشفى {$hospital->name} يطلب دم لفصيلة {$request->blood_type} في مدينتك. هل تستطيع التبرع؟";

            Notification::create([
                'user_id' => $donor->id,
                'title' => '🩸 يوجد طلب تبرع بالدم',
                'body' => $body,
                'type' => 'blood_request_donor_alert',
                'is_read' => false,
                'request_id' => $request->id,
            ]);

            if ($donor->fcm_token) {
                FCMService::send(
                    $donor->fcm_token,
                    '🩸 طلب تبرع بالدم',
                    $body,
                    [
                        'type' => 'donor_alert',
                        'request_id' => (string) $request->id,
                    ]
                );
            }
        }
    }

    /* =====================================================
     |  ✅❌ رد المتبرع
     ===================================================== */
    public function respondToRequest(Request $request, $id)
    {
        $request->validate([
            'response' => 'required|in:accepted,unavailable',
        ]);

        if ((int) Auth::user()->role_id !== 3) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        return DB::transaction(function () use ($request, $id) {

            $bloodRequest = BloodRequest::lockForUpdate()->findOrFail($id);

            // ✅ المتبرع يرد فقط إذا كان الطلب approved
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
                // إنشاء السجل إذا لم يكن موجوداً
                $pivot = RequestUser::create([
                    'blood_request_id' => $id,
                    'user_id' => Auth::id(),
                    'role_in_request' => 'donor',
                    'status' => 'pending',
                ]);
            }


            if ($pivot->status !== 'pending') {
                return response()->json(['message' => 'تم الرد مسبقاً'], 409);
            }

            $pivot->update([
                'status' => $request->response,
                'responded_at' => now(),
            ]);

            // =================================================
            // عند موافقة المتبرع
            // =================================================
            if ($request->response === 'accepted') {

                // إغلاق الطلب
                $bloodRequest->update(['status' => 'completed']);
                $this->logStatus($bloodRequest, 'approved', 'completed', Auth::id());

                // رفض بقية المتبرعين
                RequestUser::where('blood_request_id', $id)
                    ->where('user_id', '!=', Auth::id())
                    ->where('status', 'pending')
                    ->update(['status' => 'unavailable']);

                // 🔔 إشعار المستشفى (اسم + هاتف المتبرع)
                $donor = Auth::user();
                $hospitalUser = $bloodRequest->hospital->user;

                $donorName = $donor->full_name ?? $donor->name;
                $donorPhone = $donor->phone ?? 'غير متوفر';

                Notification::create([
                    'user_id' => $hospitalUser->id,
                    'title' => '🩸 متبرع وافق على الطلب',
                    'body' => "المتبرع {$donorName} وافق على التبرع.\nرقم الهاتف: {$donorPhone}",
                    'type' => 'donor_accepted',
                    'is_read' => false,
                    'request_id' => $bloodRequest->id,
                ]);

                if ($hospitalUser->fcm_token) {
                    FCMService::send(
                        $hospitalUser->fcm_token,
                        '🩸 متبرع وافق على الطلب',
                        "{$donorName} وافق على التبرع – هاتف: {$donorPhone}",
                        [
                            'type' => 'donor_accepted',
                            'request_id' => (string) $bloodRequest->id,
                            'donor_id' => (string) $donor->id,
                        ]
                    );
                }

                // إشعار المريض
                $this->notifyUser(
                    User::find($bloodRequest->requester_id),
                    'تم تأكيد التبرع ❤️',
                    'تم العثور على متبرع مناسب، سيتم التواصل معه قريباً.',
                    $bloodRequest
                );
            }

            return response()->json(['success' => true]);
        });
    }

    /* =====================================================
     |  📝 تسجيل تغيير الحالة
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
     |  🔔 إشعار مستخدم
     ===================================================== */
    private function notifyUser(User $user, string $title, string $body, ?BloodRequest $request = null)
    {
        Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => 'blood_request',
            'is_read' => false,
            'request_id' => optional($request)->id,
        ]);
    }
}
