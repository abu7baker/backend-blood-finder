<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Hospital;

class LoginController extends Controller
{
    // عرض صفحة تسجيل الدخول
    public function loginPage()
    {
        return view('admin.auth.login');
    }

    // تنفيذ عملية تسجيل الدخول
    public function login(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required',
            'password'       => 'required'
        ]);

        // تحديد نوع الحقل (إيميل أو هاتف)
        $field = filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL)
                    ? 'email'
                    : 'phone';

        $credentials = [
            $field    => $request->email_or_phone,
            'password' => $request->password
        ];

        // محاولة تسجيل الدخول
        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            /* ============================================
                1) الأدمن
            ============================================= */
            if ($user->role_id == 1) {
                return redirect()->route('admin.dashboard');
            }

            /* ============================================
                2) المستشفى
            ============================================= */
            if ($user->role_id == 2) {

                // يوجد مستشفى مرتبط بالمستخدم؟
                $hospital = Hospital::where('user_id', $user->id)->first();

                if (!$hospital) {
                    Auth::logout();
                    return back()->with('error', 'هذا الحساب غير مرتبط بأي مستشفى!');
                }

                // حالة المستشفى
                if ($hospital->status == 'pending') {
                    return redirect()->route('hospital.pending');
                }

                if ($hospital->status == 'rejected') {
                    Auth::logout();
                    return back()->with('error', 'تم رفض طلب المستشفى من قبل الإدارة.');
                }

                if ($hospital->status == 'verified') {
                    return redirect()->route('hospital.dashboard');
                }

                // لو حالة غير معروفة (نادراً)
                Auth::logout();
                return back()->with('error', 'حدثت مشكلة في حالة المستشفى، يرجى التواصل مع الإدارة.');
            }

            /* ============================================
                3) مستخدم التطبيق — ممنوع دخوله للوح التحكم
            ============================================= */
            Auth::logout();
            return back()->with('error', 'لا يمكنك الدخول إلى لوحة التحكم.');
        }

        // فشل تسجيل الدخول
        return back()->with('error', 'بيانات الدخول غير صحيحة.');
    }

    // تسجيل خروج
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
