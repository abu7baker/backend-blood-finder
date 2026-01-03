<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\BloodRequest;
use App\Models\Donation;
use App\Models\BloodStock;
use App\Models\Hospital;
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

        /* =========================
            سجل نشاط
        ========================== */

        $this->logActivity(
            'view',
            'دخول لوحة تحكم المستشفى: ' . $hospital->name
        );

        return view('hospital.dashboard.index', compact(
            'hospital',
            'stats'
        ));
    }
}
