<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Notification; // ✅ إضافة مهمة
use App\Services\FCMService;

class AppointmentController extends Controller
{
    /**
     * 🔥 إنشاء موعد تبرع جديد + إرسال إشعار
     */
    public function store(Request $request)
    {
        $request->validate([
            'hospital_id' => 'required|exists:hospitals,id',
            'date' => 'required|date',
            'time' => 'required',
        ]);

        $dateTime = "{$request->date} {$request->time}:00";
        $hospital = Hospital::find($request->hospital_id);

        if ($hospital->status !== 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'This hospital is not approved.',
            ], 403);
        }

        // 1️⃣ إنشاء الموعد
        $appointment = Appointment::create([
            'donor_id' => auth()->id(),
            'hospital_id' => $request->hospital_id,
            'date_time' => $dateTime,
            'status' => 'pending',
        ]);

        $donor = User::find(auth()->id());

        // 2️⃣ حفظ إشعار قاعدة البيانات (سريع)
        Notification::create([
            'user_id' => $donor->id,
            'title' => 'تم استلام طلب موعدك',
            'body' => "تم إنشاء موعدك في مستشفى {$hospital->name} وسيتم مراجعته.",
            'type' => 'appointment_created',
            'is_read' => false,
        ]);

        // 3️⃣ تجهيز بيانات الإشعارات
        $donorToken = $donor->fcm_token;
        $hospitalUser = User::find($hospital->user_id);
        $hospitalToken = $hospitalUser?->fcm_token;

        // 4️⃣ أرسل الرد فورًا ⚡ (هنا حل المشكلة)
        $response = response()->json([
            'success' => true,
            'message' => 'تم حجز التبرع بنجاح',
            'appointment' => $appointment,
        ], 201);

        // 5️⃣ إرسال الإشعارات في الخلفية 🔥
      

        return $response;
    }

    /**
     * 🔥 قبول الموعد + إشعار للمتبرع
     */
    public function approve($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'approved']);

        $donor = User::find($appointment->donor_id);

        // 🔔 حفظ إشعار DB
        Notification::create([
            'user_id' => $donor->id,
            'title' => 'تم قبول الموعد',
            'body' => 'تمت الموافقة على موعد التبرع الخاص بك، نلقاك في الوقت المحدد ❤️',
            'type' => 'appointment_approved',
            'is_read' => false,
        ]);

        // 🔔 FCM
        if ($donor && $donor->fcm_token) {
            FCMService::send(
                $donor->fcm_token,
                "تم قبول الموعد",
                "تمت الموافقة على موعد التبرع الخاص بك، نلقاك في الوقت المحدد ❤️",
                [
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                    "type" => "appointment_approved",
                    "appointment_id" => (string) $appointment->id,
                    "donor_id" => (string) $donor->id,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Appointment approved'
        ]);
    }

    /**
     * 🔥 رفض الموعد + إشعار للمتبرع
     */
    public function reject($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'rejected']);

        $donor = User::find($appointment->donor_id);

        // 🔔 حفظ إشعار DB
        Notification::create([
            'user_id' => $donor->id,
            'title' => 'تم رفض الموعد',
            'body' => 'نأسف، تم رفض موعدك. الرجاء اختيار وقت آخر.',
            'type' => 'appointment_rejected',
            'is_read' => false,
        ]);

        // 🔔 FCM
        if ($donor && $donor->fcm_token) {
            FCMService::send(
                $donor->fcm_token,
                "تم رفض الموعد",
                "نأسف، تم رفض موعدك. الرجاء اختيار وقت آخر."
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Appointment rejected'
        ]);
    }

    /**
     * 🔥 اكتمال التبرع + إشعار شكر للمتبرع
     */
    public function complete($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'completed']);

        $donor = User::find($appointment->donor_id);

        // 🔔 حفظ إشعار DB
        Notification::create([
            'user_id' => $donor->id,
            'title' => 'شكرًا لتبرعك ❤️',
            'body' => 'لقد ساهمت في إنقاذ حياة شخص ما! نشكرك من القلب ❤️',
            'type' => 'appointment_completed',
            'is_read' => false,
        ]);

        // 🔔 FCM
        if ($donor && $donor->fcm_token) {
            FCMService::send(
                $donor->fcm_token,
                "شكرًا لتبرعك ❤️",
                "لقد ساهمت في إنقاذ حياة شخص ما! نشكرك من القلب ❤️"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Appointment completed'
        ]);
    }

    /**
     * 📌 مواعيد المستخدم
     */
    public function myAppointments()
    {
        $appointments = Appointment::with('hospital:id,name')
            ->where('donor_id', auth()->id())
            ->orderBy('date_time', 'DESC')
            ->get();

        return response()->json([
            'success' => true,
            'appointments' => $appointments,
        ]);
    }
}
