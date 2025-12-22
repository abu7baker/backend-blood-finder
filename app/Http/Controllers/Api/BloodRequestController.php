<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\RequestStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FcmService;


class BloodRequestController extends Controller
{
    /**
     * 🩸 إنشاء طلب دم من التطبيق
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

        // تسجيل الحالة الأولى
        RequestStatusHistory::create([
            'request_id' => $bloodRequest->id,
            'old_status' => null,
            'new_status' => 'pending',
            'changed_by' => Auth::id(),
            'changed_at' => now(),
        ]);

        return response()->json([
            'message' => 'تم إرسال طلب الدم بنجاح',
            'data'    => $bloodRequest,
        ], 201);
    }

    /**
     * 📄 عرض جميع طلبات المستخدم (طلباتي)
     * 🔥 جاهز لعرض كارد "تم القبول"
     */
    public function index()
    {
        $requests = BloodRequest::with('hospital')
            ->where('requester_id', Auth::id())
            ->latest()
            ->get()
            ->map(function ($req) {
                return [
                    'id'              => $req->id,
                    'hospital'        => $req->hospital->name,
                    'blood_type'      => $req->blood_type,
                    'units_requested' => $req->units_requested,
                    'priority'        => $req->priority,
                    'status'          => $req->status, // pending / approved / rejected
                    'status_label'    => match ($req->status) {
                        'approved'  => 'تم القبول',
                        'rejected'  => 'مرفوض',
                        'completed' => 'مكتمل',
                        default     => 'قيد المراجعة',
                    },
                    'created_at'      => $req->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'data' => $requests,
        ]);
    }

    /**
     * 🔍 تفاصيل طلب دم واحد
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
            'data' => $bloodRequest,
        ]);
    }

    /**
     * ❌ إلغاء طلب دم
     */
    public function cancel($id)
    {
        $bloodRequest = BloodRequest::where('requester_id', Auth::id())
            ->whereIn('status', ['pending', 'approved'])
            ->findOrFail($id);

        $oldStatus = $bloodRequest->status;

        $bloodRequest->update([
            'status' => 'cancelled',
        ]);

        RequestStatusHistory::create([
            'request_id' => $bloodRequest->id,
            'old_status' => $oldStatus,
            'new_status' => 'cancelled',
            'changed_by' => Auth::id(),
            'changed_at' => now(),
        ]);

        return response()->json([
            'message' => 'تم إلغاء طلب الدم بنجاح',
        ]);
    }

    /**
     * 🔔 يتم استدعاؤها من المستشفى عند تغيير الحالة
     * ترسل إشعار FCM للمستخدم
     */
    public function notifyUserStatusChange(BloodRequest $request)
    {
        $user = $request->requester;

        if (!$user || !$user->fcm_token) {
            return;
        }

        $title = 'تحديث حالة طلب الدم';
        $body  = match ($request->status) {
            'approved'  => 'تم قبول طلبك من المستشفى ✅',
            'rejected'  => 'تم رفض طلبك ❌',
            'completed' => 'تم إكمال طلب الدم 🩸',
            default     => 'تم تحديث حالة طلبك',
        };

        app(FcmService::class)->send(
            $user->fcm_token,
            $title,
            $body,
            [
                'request_id' => $request->id,
                'status'     => $request->status,
            ]
        );
    }
}
