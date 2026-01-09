<?php

namespace App\Http\Controllers\Hospital\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\Donation;
use App\Models\BloodStock;
use App\Models\Hospital;
use App\Models\Appointment;
use App\Models\Notification;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Auth;

class HospitalDashboardController extends Controller
{
    use LogsActivity;

    /**
     * عرض لوحة تحكم المستشفى
     */
    public function index()
    {
        $user = Auth::user();

        // حماية
        if (!$user || !$user->hospital) {
            return redirect()->route('login')
                ->with('error', 'لا يمكنك الوصول إلى لوحة التحكم.');
        }

        $hospital = $user->hospital;

        /* =========================
            الإحصائيات
        ========================== */

        $stats = [
            // طلبات الدم
            'total_requests' => BloodRequest::where('hospital_id', $hospital->id)->count(),
            'pending_requests' => BloodRequest::where('hospital_id', $hospital->id)
                ->where('status', 'pending')->count(),
            'completed_requests' => BloodRequest::where('hospital_id', $hospital->id)
                ->where('status', 'completed')->count(),

            // التبرعات
            'total_donations' => Donation::where('hospital_id', $hospital->id)->count(),
            'completed_donations' => Donation::where('hospital_id', $hospital->id)
                ->where('status', 'completed')->count(),

            // المخزون
            'total_units' => BloodStock::where('hospital_id', $hospital->id)
                ->sum('units_available'),
        ];
        $requests_count = $stats['total_requests'];
        $stock_count = $stats['total_units'];
        $appointments_count = Appointment::where('hospital_id', $hospital->id)->count();
        $notifications_count = Notification::where('user_id', $user->id)->count();

        /* =========================
            سجل نشاط
        ========================== */

        $this->logActivity(
            'view',
            'دخول لوحة تحكم المستشفى: ' . $hospital->name
        );

        return view('hospital.dashboard.index', compact(
            'hospital',
            'stats',
            'requests_count',
            'stock_count',
            'appointments_count',
            'notifications_count'
        ));
    }
}
