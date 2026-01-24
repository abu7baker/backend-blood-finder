<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BloodStock;
use App\Models\Donation;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use App\Services\FCMService;

class HospitalAppointmentController extends Controller
{
    use LogsActivity;

    /**
     * عرض جميع مواعيد المستشفى
     */
    public function index(Request $request)
    {
        $hospitalId = auth()->user()->hospital->id;

        $appointments = Appointment::with('donor')
            ->where('hospital_id', $hospitalId)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('date_time', 'ASC')
            ->get();

        // 📝 سجل نشاط
        $this->logActivity(
            'view',
            'عرض مواعيد التبرع الخاصة بالمستشفى'
        );

        return view('hospital.appointments.index', compact('appointments'));
    }

    /**
     * بيانات الموعد (AJAX)
     */
    public function showJson($id)
    {
        $app = Appointment::with('donor')->findOrFail($id);

        return response()->json([
            'id'         => $app->id,
            'donor_name' => $app->donor->full_name,
            'phone'      => $app->donor->phone,
            'blood_type' => $app->donor->blood_type,
            'date_time'  => $app->date_time->format('Y-m-d h:i A'),
            'status'     => $app->status,
        ]);
    }

    /**
     * تحديث حالة الموعد + تحديث المخزون + تسجيل التبرع + إشعار FCM
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:appointments,id',
            'status' => 'required|in:pending,approved,cancelled,completed',
        ]);

        $appointment = Appointment::with(['donor', 'hospital'])->findOrFail($request->id);
        $donor       = $appointment->donor;
        $hospital    = $appointment->hospital;

        $oldStatus = $appointment->status;

        // تحديث حالة الموعد
        $appointment->update([
            'status' => $request->status
        ]);

        // 📝 سجل تغيير الحالة
        $this->logActivity(
            'update',
            'تغيير حالة موعد تبرع للمتبرع: ' . $donor->full_name .
            ' (' . $oldStatus . ' → ' . $request->status . ')'
        );

        /*
        |------------------------------------------------------------
        | عند اكتمال الموعد → تحديث المخزون + تسجيل التبرع
        |------------------------------------------------------------
        */
        if ($request->status === 'completed') {

            $bloodType = $donor->blood_type;

            if ($bloodType) {

                $stock = BloodStock::firstOrCreate(
                    [
                        'hospital_id' => $hospital->id,
                        'blood_type'  => $bloodType,
                    ],
                    [
                        'units_available' => 0,
                    ]
                );

                $stock->increment('units_available');

                Donation::create([
                    'donor_id'      => $donor->id,
                    'hospital_id'   => $hospital->id,
                    'request_id'    => null,
                    'blood_type'    => $bloodType,
                    'units_donated' => 1,
                    'donated_at'    => now(),
                    'status'        => 'completed',
                    'source'        => 'appointment',
                ]);

                // 📝 سجل نشاط إضافي
                $this->logActivity(
                    'create',
                    'اكتمال موعد تبرع وإضافة وحدة دم (' . $bloodType . ')'
                );
            }
        }

        /*
        |------------------------------------------------------------
        | إرسال إشعار FCM للمتبرع
        |------------------------------------------------------------
        */
        if ($donor->fcm_token) {

            switch ($request->status) {
                case 'approved':
                    $title = 'تم قبول الموعد';
                    $body  = 'تمت الموافقة على موعد التبرع الخاص بك ❤️';
                    break;

                case 'cancelled':
                    $title = 'تم إلغاء الموعد';
                    $body  = 'نأسف، تم إلغاء موعدك من قبل المستشفى.';
                    break;

                case 'completed':
                    $title = 'شكراً لتبرعك ❤️';
                    $body  = 'اكتملت عملية التبرع، نشكرك على إنقاذ الأرواح.';
                    break;

                default:
                    $title = 'تحديث موعد التبرع';
                    $body  = 'تم تحديث حالة الموعد.';
            }

            FCMService::send(
                $donor->fcm_token,
                $title,
                $body,
                [
                    'appointment_id' => (string) $appointment->id,
                    'type'           => $request->status,
                ]
            );

            $donor->notifications()->create([
                'title'   => $title,
                'body'    => $body,
                'type'    => $request->status,
                'is_read' => 0,
            ]);
        }

        return back()->with(
            'success',
            'تم تحديث حالة الموعد وإضافة التبرع وتحديث المخزون بنجاح 🎉'
        );
    }
}
