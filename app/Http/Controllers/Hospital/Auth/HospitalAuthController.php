<?php

namespace App\Http\Controllers\Hospital\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Hospital;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class HospitalAuthController extends Controller
{
    use LogsActivity;

    /* ================================
        STEP 1 - عرض الصفحة
    =================================*/
    public function registerStep1()
    {
        return view('hospital.auth.register-step1');
    }

    /* ================================
        STEP 1 - معالجة البيانات
    =================================*/
    public function registerStep1Post(Request $request)
    {
        $request->validate([
            "name"     => "required|string|max:255",
            "email"    => "required|email|unique:users,email",
            "phone"    => "required|string|min:9|max:20|unique:users,phone",
            "password" => "required|string|min:6",
            "city"     => "required|string|max:255",
            "location" => "required|string|max:255",
        ]);

        // حفظ البيانات في السيشن فقط (لا نسجل نشاط هنا)
        Session::put("hospital_register", [
            "name"     => $request->name,
            "email"    => $request->email,
            "phone"    => $request->phone,
            "password" => $request->password,
            "city"     => $request->city,
            "location" => $request->location,
        ]);

        return redirect()->route("hospital.register.step2");
    }

    /* ================================
        STEP 2 - صفحة المراجعة
    =================================*/
    public function registerStep2()
    {
        $data = Session::get("hospital_register");

        if (!$data) {
            return redirect()->route("hospital.register.step1");
        }

        return view('hospital.auth.register-step2', compact('data'));
    }

    /* ================================
        STEP 2 - إنشاء الحساب فعليًا
    =================================*/
    public function registerStep2Post(Request $request)
    {
        $data = Session::get("hospital_register");

        if (!$data) {
            return redirect()->route("hospital.register.step1");
        }

        // إنشاء المستخدم
        $user = User::create([
            "full_name"   => $data["name"],
            "email"       => $data["email"],
            "phone"       => $data["phone"],
            "password"    => Hash::make($data["password"]),
            "role_id"     => 2, // مستشفى
            "is_verified" => 0,
            "status"      => 'active',
        ]);

        // إنشاء المستشفى
        $hospital = Hospital::create([
            "user_id"  => $user->id,
            "name"     => $data["name"],
            "city"     => $data["city"],
            "location" => $data["location"],
            "status"   => "pending",
        ]);

        // ✅ سجل نشاط (حدث حقيقي)
        $this->logActivity(
            'create',
            'تسجيل مستشفى جديد: ' . $hospital->name .
            ' (المدينة: ' . $hospital->city . ')'
        );

        // تنظيف السيشن
        Session::forget("hospital_register");

        return redirect()->route("hospital.pending");
    }

    /* ================================
        صفحة الحساب قيد المراجعة
    =================================*/
    public function pending()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $hospital = Hospital::where('user_id', $user->id)->first();

        // ✅ سجل نشاط
        $this->logActivity(
            'view',
            'دخول صفحة الحساب قيد المراجعة للمستشفى: ' . ($hospital->name ?? 'غير معروف')
        );

        return view('hospital.auth.pending', compact('user', 'hospital'));
    }

    /* ================================
        فحص حالة الحساب
    =================================*/
    public function checkStatus(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'الرجاء تسجيل الدخول أولاً.');
        }

        $hospital = Hospital::where('user_id', $user->id)->first();

        if (!$hospital) {
            return back()->with('error', 'لم يتم العثور على حساب المستشفى.');
        }

        // إذا تم التفعيل
        if ($hospital->status === 'verified') {

            $this->logActivity(
                'update',
                'تم تفعيل حساب المستشفى: ' . $hospital->name
            );

            return redirect()->route('hospital.dashboard')
                ->with('success', 'تم تفعيل حسابك! مرحباً بك.');
        }

        // ما زال قيد المراجعة
        $this->logActivity(
            'view',
            'محاولة فحص حالة حساب المستشفى (قيد المراجعة): ' . $hospital->name
        );

        return back()->with('warning', 'ما زال حسابك قيد المراجعة. الرجاء المحاولة لاحقاً.');
    }

    /* ================================
        تسجيل خروج
    =================================*/
    public function logout()
    {
        $user = Auth::user();

        if ($user) {
            $this->logActivity(
                'logout',
                'تسجيل خروج المستشفى: ' . $user->full_name
            );
        }

        Auth::logout();
        return redirect()->route('login');
    }
}
