<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Hospital;
use App\Traits\LogsActivity;

class LoginController extends Controller
{
    use LogsActivity;

    /**
     * عرض صفحة تسجيل الدخول
     */
    public function loginPage()
    {
        return view('admin.auth.login');
    }

    /**
     * تنفيذ عملية تسجيل الدخول
     */
    public function login(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required',
            'password'       => 'required'
        ]);

        // تحديد نوع الحقل (بريد أو هاتف)
        $field = filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'phone';

        $credentials = [
            $field     => $request->email_or_phone,
            'password' => $request->password
        ];

        // محاولة تسجيل الدخول
        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            /* ============================================
                1) مدير النظام (Admin)
            ============================================= */
            if ($user->role->name === 'admin') {

                // ✅ تسجيل النشاط
                $this->logActivity(
                    'login',
                    'تسجيل دخول ناجح إلى لوحة تحكم الأدمن'
                );

                return redirect()->route('admin.dashboard');
            }

            /* ============================================
                2) حساب مستشفى
            ============================================= */
            if ($user->role->name === 'hospital') {

                $hospital = Hospital::where('user_id', $user->id)->first();

                if (!$hospital) {
                    Auth::logout();

                    $this->logActivity(
                        'login_failed',
                        'محاولة دخول بحساب مستشفى غير مرتبط'
                    );

                    return back()->with('error', 'هذا الحساب غير مرتبط بأي مستشفى.');
                }

                if ($hospital->status === 'pending') {

                    $this->logActivity(
                        'login',
                        'دخول مستشفى قيد المراجعة'
                    );

                    return redirect()->route('hospital.pending');
                }

                if ($hospital->status === 'rejected') {
                    Auth::logout();

                    $this->logActivity(
                        'login_failed',
                        'محاولة دخول مستشفى مرفوض'
                    );

                    return back()->with('error', 'تم رفض طلب المستشفى من قبل الإدارة.');
                }

                if ($hospital->status === 'verified') {

                    $this->logActivity(
                        'login',
                        'تسجيل دخول ناجح لمستشفى ' . $hospital->name
                    );

                    return redirect()->route('hospital.dashboard');
                }

                Auth::logout();
                return back()->with('error', 'حالة المستشفى غير معروفة، يرجى التواصل مع الإدارة.');
            }

            /* ============================================
                3) مستخدم عادي (ممنوع من لوحة التحكم)
            ============================================= */
            Auth::logout();

            $this->logActivity(
                'login_failed',
                'محاولة دخول غير مصرح بها للوحة التحكم'
            );

            return back()->with('error', 'لا تملك صلاحية الدخول إلى لوحة التحكم.');
        }

        // ❌ فشل تسجيل الدخول
        return back()->with('error', 'بيانات الدخول غير صحيحة.');
    }

    /**
     * تسجيل الخروج
     */
    public function logout()
    {
        // ✅ تسجيل النشاط قبل الخروج
        $this->logActivity(
            'logout',
            'تسجيل خروج من لوحة التحكم'
        );

        Auth::logout();
        return redirect()->route('login');
    }
}
