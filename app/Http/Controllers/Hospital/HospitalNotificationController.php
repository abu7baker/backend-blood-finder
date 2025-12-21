<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class HospitalNotificationController extends Controller
{
    /**
     * ✔ عرض قائمة الإشعارات للمستشفى
     */
    public function index()
    {
        $hospitalUserId = Auth::id(); // المستخدم الحالي (المستشفى)

        $notifications = Notification::where('user_id', $hospitalUserId)
            ->latestFirst()
            ->get();

        $unreadCount = Notification::where('user_id', $hospitalUserId)
            ->unread()
            ->count();

        return view('hospital.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * ✔ عرض إشعار وتحديده كمقروء
     */
    public function show($id)
    {
        $notification = Notification::findOrFail($id);

        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }

        return view('hospital.notifications.show', compact('notification'));
    }

    /**
     * ✔ تعيين كل الإشعارات كمقروءة
     */
    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return back()->with('success', 'تم تحديد كل الإشعارات كمقروءة ✔');
    }
}
