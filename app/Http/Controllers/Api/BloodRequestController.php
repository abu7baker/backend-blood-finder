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
use App\Services\FCMService;

class BloodRequestController extends Controller
{
    /* =====================================================
     |  🩸 إنشاء طلب دم (من المستخدم)
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

        return response()->json(['success' => true, 'data' => $bloodRequest], 201);
    }

    /* =====================================================
     |  🔔 تغيير حالة الطلب (من المستشفى)
     ===================================================== */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,completed',
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
        if ($bloodRequest->status === $newStatus) return;

        $oldStatus = $bloodRequest->status;
        $bloodRequest->update(['status' => $newStatus]);

        $this->logStatus($bloodRequest, $oldStatus, $newStatus, $changedBy);

        // إشعار المريض
        $requester = User::find($bloodRequest->requester_id);
        if ($requester) {
            $this->notifyUser(
                $requester,
                'تمت الموافقة على طلب الدم 🩸',
                'تمت الموافقة وسيتم إشعار المتبرعين المناسبين.',
                $bloodRequest
            );
        }

        if ($newStatus === 'approved') {
            $this->notifyEligibleDonors($bloodRequest);
        }
    }

    /* =====================================================
     |  🧑‍🦰 إشعار المتبرعين + إنشاء RequestUser
     ===================================================== */
    private function notifyEligibleDonors(BloodRequest $request)
    {
        $city = User::where('id', $request->hospital_id)->value('city');

        $donors = User::eligibleDonors()
            ->where('blood_type', $request->blood_type)
            ->where('city', $city)
            ->get();

        foreach ($donors as $donor) {

            // منع التكرار
            $exists = RequestUser::where('request_id', $request->id)
                ->where('user_id', $donor->id)
                ->exists();

            if ($exists) continue;

            RequestUser::create([
                'request_id'      => $request->id,
                'user_id'         => $donor->id,
                'role_in_request' => 'donor',
                'response_status' => 'pending',
            ]);

            Notification::create([
                'user_id'    => $donor->id,
                'title'      => '🩸 يوجد طلب تبرع بالدم',
                'body'       => "يوجد طلب دم لفصيلة {$request->blood_type} في مدينتك. هل تستطيع التبرع؟",
                'type'       => 'blood_request_donor_alert',
                'is_read'    => false,
                'request_id' => $request->id, // 🔥 مهم لفلتر
            ]);

            if ($donor->fcm_token) {
                FCMService::send(
                    $donor->fcm_token,
                    '🩸 طلب تبرع بالدم',
                    'اضغط للموافقة أو الرفض',
                    [
                        'type'       => 'donor_alert',
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
            'response' => 'required|in:accepted,rejected',
        ]);

        // فقط متبرع
        if (Auth::user()->role_id !== 3) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $pivot = RequestUser::where('request_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($pivot->response_status !== 'pending') {
            return response()->json(['message' => 'تم الرد مسبقًا'], 409);
        }

        $pivot->update([
            'response_status' => $request->response,
            'responded_at'    => now(),
        ]);

        if ($request->response === 'accepted') {

            $bloodRequest = BloodRequest::findOrFail($id);

            // إغلاق الطلب
            $bloodRequest->update(['status' => 'completed']);
            $this->logStatus($bloodRequest, 'approved', 'completed', Auth::id());

            // رفض بقية المتبرعين
            RequestUser::where('request_id', $id)
                ->where('user_id', '!=', Auth::id())
                ->update(['response_status' => 'rejected']);

            // إشعار المستشفى والمريض
            $this->notifyUser(
                User::find($bloodRequest->hospital_id),
                'تم العثور على متبرع 🩸',
                'تمت موافقة أحد المتبرعين على الطلب.',
                $bloodRequest
            );

            $this->notifyUser(
                User::find($bloodRequest->requester_id),
                'تم تأكيد التبرع ❤️',
                'تم العثور على متبرع مناسب، نسأل الله لك الشفاء.',
                $bloodRequest
            );
        }

        return response()->json(['success' => true]);
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
            'user_id'    => $user->id,
            'title'      => $title,
            'body'       => $body,
            'type'       => 'blood_request',
            'is_read'    => false,
            'request_id' => optional($request)->id,
        ]);
    }
}
