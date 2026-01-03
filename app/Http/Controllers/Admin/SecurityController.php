<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LogsActivity;
use App\Models\Role;


use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SecurityController extends Controller
{
    use LogsActivity;

    public function index()
    {
        /* =====================================================
         | 1️⃣ إحصائيات الأمان (الكروت العلوية)
         ===================================================== */

        // عدد الأدمن النشطين
        $activeAdmins = User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })
            ->where('status', 'active')
            ->count();

        // عدد الجلسات النشطة
        $activeSessionsCount = DB::table('sessions')->count();

        // عدد الأدوار (Roles)
        $rolesCount = Role::count();

        // محاولات الدخول اليوم (اعتمادًا على last_activity)
        $todayLogins = DB::table('sessions')
            ->whereDate(
                DB::raw('FROM_UNIXTIME(last_activity)'),
                Carbon::today()
            )
            ->count();

        // محاولات فاشلة (جاهزة للتوسعة لاحقًا)
        $failedLogins = 0;


        /* =====================================================
         | 2️⃣ الأدوار (Roles Tab)
         ===================================================== */

        // جميع الأدوار + عدد المستخدمين لكل دور
        $roles = Role::withCount('users')
            ->orderBy('id')
            ->get();
        $activityLogs = ActivityLog::latest()->get()->map(function ($log) {
            $log->label = $this->activityLabel($log->action);
            $log->color = $this->activityColor($log->action);
            return $log;
        });


        /* =====================================================
         | 3️⃣ الجلسات النشطة (Sessions Tab)
         ===================================================== */

        $activeSessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->select(
                'sessions.id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'users.full_name',
                'roles.name as role_name'
            )
            ->orderByDesc('sessions.last_activity')
            ->get();


        /* =====================================================
         | 4️⃣ تمرير جميع البيانات للـ View
         ===================================================== */

        return view('admin.security.index', compact(
            'activeAdmins',
            'activeSessionsCount',
            'rolesCount',
            'todayLogins',
            'failedLogins',
            'roles',
            'activeSessions'
            ,
            'activityLogs'
        ));
    }
}
