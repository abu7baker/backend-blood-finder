<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Notification;
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

        // 2️⃣ حفظ إشعار قاعدة البيانات
        Notification::create([
            'user_id' => $donor->id,
            'title' => 'تم استلام طلب موعدك',
            'body' => "تم إنشاء موعدك في مستشفى {$hospital->name} وسيتم مراجعته.",
            'type' => 'appointment_created',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $hospital->user_id,
            'title' => 'New donation appointment',
            'body' => "New donation appointment from {$donor->full_name} on {$dateTime}.",
            'type' => 'appointment',
            'is_read' => false,
        ]);

        // 3️⃣ تجهيز بيانات الإشعارات
        $donorToken = $donor->fcm_token;
        $hospitalUser = User::find($hospital->user_id);
        $hospitalToken = $hospitalUser?->fcm_token;

        // 4️⃣ أرسل الرد فورًا
        $response = response()->json([
            'success' => true,
            'message' => 'تم حجز التبرع بنجاح',
            'appointment' => $appointment,
        ], 201);

        // 5️⃣ إرسال الإشعارات (آمن – لا يكسر الطلب)
        if ($hospitalToken) {
            try {
                FCMService::send(
                    $hospitalToken,
                    'موعد تبرع جديد',
                    'لديك طلب موعد تبرع جديد',
                    [
                        'type' => 'appointment_created',
                        'appointment_id' => (string) $appointment->id,
                    ]
                );
            } catch (\Throwable $e) {
                logger('FCM STORE ERROR (HOSPITAL): ' . $e->getMessage());
            }
        }

        if ($donorToken) {
            try {
                FCMService::send(
                    $donorToken,
                    'تم إنشاء الموعد',
                    'تم إنشاء موعد تبرعك بنجاح',
                    [
                        'type' => 'appointment_created',
                        'appointment_id' => (string) $appointment->id,
                    ]
                );
            } catch (\Throwable $e) {
                logger('FCM STORE ERROR (DONOR): ' . $e->getMessage());
            }
        }

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

        // 🔔 FCM (آمن)
        if ($donor && $donor->fcm_token) {
            try {
                FCMService::send(
                    $donor->fcm_token,
                    'تم قبول الموعد',
                    'تمت الموافقة على موعد التبرع الخاص بك، نلقاك في الوقت المحدد ❤️',
                    [
                        'type' => 'appointment_approved',
                        'appointment_id' => (string) $appointment->id,
                        'donor_id' => (string) $donor->id,
                    ]
                );
            } catch (\Throwable $e) {
                logger('FCM APPROVE ERROR: ' . $e->getMessage());
            }
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

        // 🔔 FCM (آمن)
        if ($donor && $donor->fcm_token) {
            try {
                FCMService::send(
                    $donor->fcm_token,
                    'تم رفض الموعد',
                    'نأسف، تم رفض موعدك. الرجاء اختيار وقت آخر.',
                    [
                        'type' => 'appointment_rejected',
                        'appointment_id' => (string) $appointment->id,
                    ]
                );
            } catch (\Throwable $e) {
                logger('FCM REJECT ERROR: ' . $e->getMessage());
            }
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

        // 🔔 FCM (آمن)
        if ($donor && $donor->fcm_token) {
            try {
                FCMService::send(
                    $donor->fcm_token,
                    'شكرًا لتبرعك ❤️',
                    'لقد ساهمت في إنقاذ حياة شخص ما! نشكرك من القلب ❤️',
                    [
                        'type' => 'appointment_completed',
                        'appointment_id' => (string) $appointment->id,
                    ]
                );
            } catch (\Throwable $e) {
                logger('FCM COMPLETE ERROR: ' . $e->getMessage());
            }
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
