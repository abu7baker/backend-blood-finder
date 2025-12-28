<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FCMService;
use App\Models\User;
use App\Models\BloodRequest;
use App\Models\RequestUser;

class NotificationController extends Controller
{
    /**
     * 📌 جلب إشعارات المستخدم (مع حالة التفاعل)
     */
    public function index()
{
    $user = auth()->user();

    $notifications = $user->notifications()
        ->latest()
        ->get()
        ->map(function ($notification) use ($user) {

            $data = [
                'id'         => $notification->id,
                'title'      => $notification->title,
                'body'       => $notification->body,
                'type'       => $notification->type,
                'is_read'    => (bool)$notification->is_read,
                'created_at'=> $notification->created_at,
                'request_id'=> $notification->request_id,
            ];

            // ==============================
            // 🩸 إشعار مرتبط بطلب دم
            // ==============================
            if ($notification->request_id) {

                $bloodRequest = BloodRequest::find($notification->request_id);

                $pivot = RequestUser::where('blood_request_id', $notification->request_id)
                    ->where('user_id', $user->id)
                    ->first();

                $requestStatus = $bloodRequest?->status;
                $myResponse    = $pivot?->status;

                // ✅ الشرط النهائي الصحيح
                $isActionable = (
                    $notification->type === 'blood_request_donor_alert'
                    && $bloodRequest
                    && $requestStatus === 'approved'
                    && $pivot
                    && $myResponse === 'pending'
                );

                $data['request_status'] = $requestStatus;
                $data['my_response']    = $myResponse;
                $data['is_actionable']  = $isActionable;
            }

            return $data;
        });

    return response()->json([
        'success'       => true,
        'notifications' => $notifications
    ]);
}


    /**
     * 📌 تعليم إشعار كمقروء
     * ⚠️ لا يؤثر على is_actionable
     */
    public function markRead($id)
    {
        $user = auth()->user();

        $notification = $user->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعليم الإشعار كمقروء'
        ]);
    }

    /**
     * 📌 تعليم جميع الإشعارات كمقروءة
     */
    public function markAllRead()
    {
        $user = auth()->user();

        $user->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعليم جميع الإشعارات كمقروءة'
        ]);
    }

    /**
     * 📌 إرسال إشعار لمستخدم محدد
     */
    public function sendToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title'   => 'required|string',
            'body'    => 'required|string',
        ]);

        $user = User::find($request->user_id);

        if (!$user->fcm_token) {
            return response()->json([
                'success' => false,
                'message' => 'هذا المستخدم لا يملك FCM Token'
            ], 404);
        }

        $res = FCMService::send(
            $user->fcm_token,
            $request->title,
            $request->body,
            [
                'type'    => 'single',
                'user_id' => (string) $user->id,
            ]
        );

        $user->notifications()->create([
            'title'   => $request->title,
            'body'    => $request->body,
            'type'    => 'single',
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الإشعار بنجاح',
            'firebase_response' => $res
        ]);
    }

    /**
     * 📌 إرسال إشعار لجميع المستخدمين
     */
    public function sendToAll(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body'  => 'required|string',
        ]);

        $users = User::whereNotNull('fcm_token')->get();

        foreach ($users as $user) {

            FCMService::send(
                $user->fcm_token,
                $request->title,
                $request->body,
                [
                    'type'    => 'broadcast',
                    'user_id' => (string) $user->id,
                ]
            );

            $user->notifications()->create([
                'title'   => $request->title,
                'body'    => $request->body,
                'type'    => 'broadcast',
                'is_read' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الإشعارات للجميع'
        ]);
    }

    /**
     * 📌 حفظ FCM Token
     */
    public function saveToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string'
        ]);

        $user = auth()->user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ FCM Token بنجاح'
        ]);
    }
}
