<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LogsActivity;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


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
        $webSessionsCount = DB::table('sessions')->count();
        $tokenSessionsCount = DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->count();
        $activeSessionsCount = $webSessionsCount + $tokenSessionsCount;

        // عدد الأدوار (Roles)
        $rolesCount = Role::count();

        // محاولات الدخول اليوم
        $todayLogins = ActivityLog::where('action', 'login')
            ->whereDate('created_at', Carbon::today())
            ->count();

        // محاولات فاشلة اليوم
        $failedLogins = ActivityLog::where('action', 'login_failed')
            ->whereDate('created_at', Carbon::today())
            ->count();


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

        $activeWebSessions = DB::table('sessions')
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
            ->get()
            ->map(function ($session) {
                $session->session_type = 'web';
                $session->device_name = 'Web';
                return $session;
            });

        $activeTokenSessions = DB::table('personal_access_tokens')
            ->leftJoin('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->where('personal_access_tokens.tokenable_type', User::class)
            ->select(
                'personal_access_tokens.id',
                'personal_access_tokens.name',
                'personal_access_tokens.device_name',
                'personal_access_tokens.ip_address',
                'personal_access_tokens.user_agent',
                'personal_access_tokens.last_used_at',
                'personal_access_tokens.created_at',
                'users.full_name',
                'roles.name as role_name'
            )
            ->orderByDesc(DB::raw('COALESCE(personal_access_tokens.last_used_at, personal_access_tokens.created_at)'))
            ->get()
            ->map(function ($token) {
                $token->session_type = 'token';
                $token->device_name = $token->device_name ?: ($token->name ?: 'App');
                $token->ip_address = $token->ip_address ?: '-';
                $token->user_agent = $token->user_agent ?: $token->device_name;
                $token->last_activity = Carbon::parse($token->last_used_at ?? $token->created_at)->timestamp;
                return $token;
            });

        $activeSessions = $activeWebSessions->concat($activeTokenSessions)
            ->sortByDesc('last_activity')
            ->values();


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

    public function destroySession(Request $request, string $type, string $id)
    {
        if ($type === 'web') {
            $session = DB::table('sessions')
                ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
                ->select('sessions.id', 'users.full_name')
                ->where('sessions.id', $id)
                ->first();

            DB::table('sessions')->where('id', $id)->delete();

            $description = $session?->full_name
                ? 'إنهاء جلسة ويب للمستخدم: ' . $session->full_name
                : 'إنهاء جلسة ويب';
            $this->logActivity('session_end', $description);

            if ($request->session()->getId() === $id) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('success', 'تم إنهاء الجلسة.');
            }
        } elseif ($type === 'token') {
            $token = DB::table('personal_access_tokens')
                ->leftJoin('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
                ->select(
                    'personal_access_tokens.id',
                    'personal_access_tokens.name',
                    'personal_access_tokens.device_name',
                    'users.full_name'
                )
                ->where('personal_access_tokens.id', $id)
                ->first();

            DB::table('personal_access_tokens')->where('id', $id)->delete();

            $deviceName = $token?->device_name ?: ($token?->name ?: 'App');
            $description = $token?->full_name
                ? 'إنهاء جلسة تطبيق للمستخدم: ' . $token->full_name . ' (' . $deviceName . ')'
                : 'إنهاء جلسة تطبيق';
            $this->logActivity('session_end', $description);
        } else {
            return back()->with('error', 'نوع الجلسة غير صالح.');
        }

        return back()->with('success', 'تم إنهاء الجلسة.');
    }
}
