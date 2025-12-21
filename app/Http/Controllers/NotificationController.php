<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FCMService;
use App\Models\User;

class NotificationController extends Controller
{
    /**
     * 📌 جلب إشعارات المستخدم
     */
    public function index()
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'notifications' => $user->notifications()->latest()->get()
        ]);
    }

    /**
     * 📌 تعليم الإشعارات كمقروءة
     */

    public function markRead($id)
{
    $user = auth()->user();

    $notification = $user->notifications()
        ->where('id', $id)
        ->firstOrFail();

    $notification->update([
        'is_read' => 1,
        'read_at' => now(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم تعليم الإشعار كمقروء'
    ]);
}

    public function markAllRead()
    {
        $user = auth()->user();

        $user->notifications()
            ->where('is_read', 0)
            ->update([
                'is_read' => 1,
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

        // 🔥 إرسال الإشعار للجهاز
        $res = FCMService::send(
            $user->fcm_token,
            $request->title,
            $request->body,
            [
                "type" => "single",
                "user_id" => (string) $user->id,
            ]
        );

        // 💾 حفظ الإشعار في قاعدة البيانات
        $user->notifications()->create([
            'title'   => $request->title,
            'body'    => $request->body,
            'type'    => 'single',
            'is_read' => 0,
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

            // إرسال الإشعار
            FCMService::send(
                $user->fcm_token,
                $request->title,
                $request->body,
                [
                    "type" => "broadcast",
                    "user_id" => (string) $user->id,
                ]
            );

            // حفظه بقاعدة البيانات
            $user->notifications()->create([
                'title'   => $request->title,
                'body'    => $request->body,
                'type'    => 'broadcast',
                'is_read' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الإشعارات للجميع'
        ]);
    }

    /**
     * 📌 حفظ FCM Token بعد تسجيل الدخول
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
