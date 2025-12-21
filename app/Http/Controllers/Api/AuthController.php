<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{
    /* =====================================================
     |  تسجيل مستخدم جديد (مع OTP عبر الإيميل)
     ===================================================== */
    public function register(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'age' => 'nullable|integer|min:18|max:70',
            'gender' => 'nullable|in:male,female',
            'city' => 'nullable|string|max:255',
            'blood_type' => 'nullable|string|max:10',
            'chronic_disease' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
        ]);

        // 🔐 توليد OTP
        $otp = rand(100000, 999999);

        $data['password'] = Hash::make($data['password']);
        $data['role_id'] = 3;
        $data['is_verified'] = false;
        $data['email_verification_code'] = $otp;
        $data['email_verification_expires_at'] = now()->addMinutes(10);

        $user = User::create($data);

        // ✉️ إرسال OTP إلى الإيميل
        Mail::raw(
            "مرحباً {$user->full_name}\n\nرمز التحقق الخاص بك هو: {$otp}\n\nالرمز صالح لمدة 10 دقائق.",
            function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('رمز التحقق من البريد الإلكتروني');
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الحساب، تم إرسال رمز التحقق إلى البريد الإلكتروني',
            'needs_verification' => true,
        ]);
    }

    /* =====================================================
     |  التحقق من رمز الإيميل (OTP)
     ===================================================== */
    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (
            !$user ||
            $user->email_verification_code !== $request->otp ||
            Carbon::now()->greaterThan($user->email_verification_expires_at)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'رمز التحقق غير صحيح أو منتهي',
            ], 422);
        }

        // ✅ تفعيل الحساب
        $user->update([
            'is_verified' => true,
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'email_verification_expires_at' => null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تفعيل الحساب بنجاح',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /* =====================================================
     |  إعادة إرسال رمز التحقق
     ===================================================== */
    public function resendEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'الحساب غير موجود أو مفعل مسبقًا',
            ], 422);
        }

        $otp = rand(100000, 999999);

        $user->update([
            'email_verification_code' => $otp,
            'email_verification_expires_at' => now()->addMinutes(10),
        ]);

        Mail::raw(
            "رمز التحقق الجديد هو: {$otp}\nالرمز صالح لمدة 10 دقائق.",
            function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('إعادة إرسال رمز التحقق');
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'تم إعادة إرسال رمز التحقق',
        ]);
    }

    /* =====================================================
     |  تسجيل الدخول (هاتف + كلمة المرور)
     ===================================================== */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (
            !$user ||
            !Hash::check($request->password, $user->password)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الهاتف أو كلمة المرور غير صحيحة',
            ], 401);
        }

        if (!$user->is_verified) {
            return response()->json([
                'success' => false,
                'needs_verification' => true,
                'message' => 'يرجى تفعيل الحساب عبر البريد الإلكتروني',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /* =====================================================
     |  تسجيل الدخول عبر Google (يبقى كما هو)
     ===================================================== */
    public function googleLogin(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'google_id' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $user = User::create([
                'full_name' => $request->name,
                'email' => $request->email,
                'google_id' => $request->google_id,
                'password' => Hash::make('google_' . rand(1000, 9999)),
                'role_id' => 3,
                'is_verified' => true,
                'email_verified_at' => now(),
                'donation_eligibility' => 'eligible',
            ]);

            $needsCompletion = true;
        } else {
            if (!$user->google_id) {
                $user->update(['google_id' => $request->google_id]);
            }

            $needsCompletion =
                is_null($user->phone) ||
                is_null($user->blood_type) ||
                is_null($user->gender) ||
                is_null($user->age) ||
                is_null($user->city);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user->fresh(),
            'needs_completion' => $needsCompletion,
        ]);
    }

 
    /* =====================================================
     |  تحديث بيانات المستخدم (إكمال البيانات)
     ===================================================== */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'phone' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:18|max:70',
            'gender' => 'nullable|in:male,female',
            'city' => 'nullable|string|max:255',
            'blood_type' => 'nullable|string|max:10',
            'chronic_disease' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
        ]);

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث البيانات بنجاح',
            'user' => $user->fresh(),
        ]);
    }

    /* =====================================================
     |  بيانات المستخدم الحالي
     ===================================================== */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user(),
        ]);
    }

    /* =====================================================
     |  تسجيل الخروج
     ===================================================== */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }
}
