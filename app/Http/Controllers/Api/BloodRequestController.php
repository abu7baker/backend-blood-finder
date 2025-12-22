<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\RequestStatusHistory;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FCMService;
use App\Models\User;

class BloodRequestController extends Controller
{
    /**
     * 🩸 إنشاء طلب دم
     */
    public function store(Request $request)
    {
        $request->validate([
            'hospital_id'     => 'required|exists:hospitals,id',
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

        RequestStatusHistory::create([
            'request_id' => $bloodRequest->id,
            'old_status' => null,
            'new_status' => 'pending',
            'changed_by' => Auth::id(),
            'changed_at' => now(),
        ]);

        Notification::create([
            'user_id' => Auth::id(),
            'title'   => 'تم إرسال طلب الدم 🩸',
            'body'    => 'تم استلام طلب الدم وسيتم مراجعته من المستشفى في أقرب وقت.',
            'type'    => 'blood_request_created',
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب الدم بنجاح',
            'data'    => $bloodRequest,
        ], 201);
    }

    /**
     * 📄 طلبات المستخدم
     */
    public function index()
    {
        $requests = BloodRequest::with('hospital')
            ->where('requester_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn ($req) => [
                'id'              => $req->id,
                'hospital'        => $req->hospital->name,
                'blood_type'      => $req->blood_type,
                'units_requested' => $req->units_requested,
                'priority'        => $req->priority,
                'status'          => $req->status,
                'status_label'    => match ($req->status) {
                    'approved'  => 'تم القبول',
                    'rejected'  => 'مرفوض',
                    'completed' => 'مكتمل',
                    'cancelled' => 'ملغي',
                    default     => 'قيد المراجعة',
                },
                'created_at' => $req->created_at->toDateTimeString(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    /**
     * 🔍 تفاصيل طلب دم
     */
    public function show($id)
    {
        $bloodRequest = BloodRequest::with([
            'hospital',
            'statusHistory',
            'responders.user',
            'donations',
        ])
        ->where('requester_id', Auth::id())
        ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $bloodRequest,
        ]);
    }

    /**
     * ❌ إلغاء الطلب
     */
    public function cancel($id)
    {
        $bloodRequest = BloodRequest::where('requester_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->findOrFail($id);

        $this->changeStatusInternal($bloodRequest, 'cancelled', Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء طلب الدم بنجاح',
        ]);
    }

    /**
     * 🔔 تغيير حالة الطلب + إشعار (يُستدعى من المستشفى)
     */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,completed',
        ]);

        $bloodRequest = BloodRequest::findOrFail($id);

        $this->changeStatusInternal($bloodRequest, $request->status, Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة طلب الدم بنجاح',
            'data' => [
                'request_id' => $bloodRequest->id,
                'status' => $bloodRequest->status,
            ]
        ]);
    }

    /**
     * 🧠 منطق موحّد لتغيير الحالة + الإشعارات
     */
    private function changeStatusInternal(BloodRequest $bloodRequest, string $newStatus, $changedBy)
    {
        $oldStatus = $bloodRequest->status;

        $bloodRequest->update(['status' => $newStatus]);

        RequestStatusHistory::create([
            'request_id' => $bloodRequest->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => $changedBy,
            'changed_at' => now(),
        ]);

        $user = User::find($bloodRequest->requester_id);

        if (!$user) return;

        $messages = match ($newStatus) {
            'approved' => [
                'title' => 'تم قبول طلب الدم 🩸',
                'body'  => 'خبر سار! المستشفى وافقت على طلب الدم الخاص بك.',
            ],
            'rejected' => [
                'title' => 'تعذر توفير الدم ❌',
                'body'  => 'نعتذر، لم يتم قبول طلب الدم حاليًا.',
            ],
            'completed' => [
                'title' => 'تم توفير الدم ❤️',
                'body'  => 'الحمد لله، تم توفير وحدات الدم المطلوبة.',
            ],
            'cancelled' => [
                'title' => 'تم إلغاء طلب الدم',
                'body'  => 'تم إلغاء طلب الدم بناءً على التحديث الأخير.',
            ],
            default => [
                'title' => 'تحديث حالة طلب الدم',
                'body'  => 'تم تحديث حالة طلب الدم الخاص بك.',
            ],
        };

        Notification::create([
            'user_id' => $user->id,
            'title'   => $messages['title'],
            'body'    => $messages['body'],
            'type'    => 'blood_request_status',
            'is_read' => false,
        ]);

        if ($user->fcm_token) {
            try {
                FCMService::send(
                    $user->fcm_token,
                    $messages['title'],
                    $messages['body'],
                    [
                        'type'       => 'blood_request',
                        'request_id' => (string) $bloodRequest->id,
                        'status'     => $newStatus,
                    ]
                );
            } catch (\Throwable $e) {
                logger('FCM BLOOD REQUEST ERROR: ' . $e->getMessage());
            }
        }
    }
}
