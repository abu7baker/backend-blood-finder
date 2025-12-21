<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Hospital;
use App\Models\BloodStock;
use App\Models\Donation;
use App\Models\BloodRequest;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // === إحصائيات أساسية ===
        $totalUsers          = User::count();
        $totalHospitals      = Hospital::count();
        $totalBloodStocks     = BloodStock::count();
        $activeDonors        = User::where('role_id', 3)->where('status', 'active')->count(); 
    

        // === إحصائيات اليوم ===
        $today = Carbon::today();

        $todayDonations       = Donation::whereDate('created_at', $today)->count();
        $todayRequests        = BloodRequest::whereDate('created_at', $today)->count();
        $activeHospitalsToday = Hospital::whereDate('updated_at', $today)->count();

        // حالات الطوارئ = طلبات دم عاجلة
        $todayEmergencies     = BloodRequest::where('priority', 'urgent')
                                            ->whereDate('created_at', $today)
                                            ->count();

        // === الإجراءات السريعة (HTML جاهز) ======p
        $quickActions = view('admin.dashboard_parts.quick_actions')->render();

        // === التنبيهات الحديثة (HTML جاهز) ===[[[]]]
        $recentAlerts = view('admin.dashboard_parts.recent_alerts')->render();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalHospitals',
            'totalBloodStocks',
            'activeDonors',
            'todayDonations',
            'todayRequests',
            'activeHospitalsToday',
            'todayEmergencies',
            'quickActions',
            'recentAlerts'
        ));
    }
}
