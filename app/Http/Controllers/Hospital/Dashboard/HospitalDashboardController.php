<?php

namespace App\Http\Controllers\Hospital\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Support\Facades\Auth;

class HospitalDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // نجيب المستشفى المرتبط بهذا المستخدم
        $hospital = Hospital::where('user_id', $user->id)->first();

        if (! $hospital) {
            // لو حصل خلل ومافيش Hospital لهذا المستخدم
            return redirect()->route('login')->with('error', 'حسابك غير مرتبط بأي مستشفى.');
        }

        $requests_count      = $hospital->bloodRequests()->count();
        $stock_count         = $hospital->bloodStock()->sum('units_available'); // أو ->count()
        $appointments_count  = $hospital->appointments()->count();
        $notifications_count = $user->notifications()->whereNull('read_at')->count();

        return view('hospital.dashboard.index', compact(
            'hospital',
            'requests_count',
            'stock_count',
            'appointments_count',
            'notifications_count'
        ));
    }
}
