<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use Throwable;

class AuthController extends Controller
{
    use LogsActivity;

    /* =====================================================
     |  تسجيل مستخدم جديد (مع OTP)
     ===================================================== */
    public function register(Request $request)
    {
        try {
            // ✅ Validation
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

            // ✅ تحويل القيم الفارغة إلى null
            foreach ($data as $key => $value) {
                if ($value === '') {
                    $data[$key] = null;
                }
            }

            // 🔐 OTP
            $otp = rand(100000, 999999);

            $data['password'] = Hash::make($data['password']);
            $data['role_id'] = 3;
            $data['donation_eligibility'] = 'eligible';
            $data['is_verified'] = false;
            $data['email_verification_code'] = $otp;
            $data['email_verification_expires_at'] = now()->addMinutes(10);

            $user = User::create($data);

            // ✉️ إرسال الإيميل
            Mail::raw(
                "مرحباً {$user->full_name}\n\nرمز التحقق: {$otp}\n\nالرمز صالح لمدة 10 دقائق.",
                fn ($message) =>
                    $message->to($user->email)
                            ->subject('رمز التحقق من البريد الإلكتروني')
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الحساب، تم إرسال رمز التحقق إلى البريد الإلكتروني',
                'needs_verification' => true,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'type' => 'validation_error',
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors(),
            ], 422);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'type' => 'database_error',
                'message' => 'خطأ في قاعدة البيانات',
                'error' => $e->getMessage(),
            ], 500);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'type' => 'server_error',
                'message' => 'حدث خطأ غير متوقع',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /* =====================================================
     |  التحقق من OTP
     ===================================================== */
    public function verifyEmailOtp(Request $request)
    {
        try {
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

            $user->update([
                'is_verified' => true,
                'email_verified_at' => now(),
                'email_verification_code' => null,
                'email_verification_expires_at' => null,
            ]);

            $token = $this->createTokenForDevice($user, $request);
            $this->logActivity('login', 'تسجيل دخول عبر التطبيق: ' . $user->full_name, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'تم تفعيل الحساب بنجاح',
                'token' => $token,
                'user' => $user,
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ أثناء التحقق',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /* =====================================================
     |  تسجيل الدخول
     ===================================================== */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required',
                'password' => 'required',
            ]);

            $user = User::where('phone', $request->phone)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                $this->logActivity(
                    'login_failed',
                    'محاولة تسجيل دخول فاشلة عبر التطبيق: ' . $request->phone,
                    $user?->id
                );

                return response()->json([
                    'success' => false,
                    'message' => 'رقم الهاتف أو كلمة المرور غير صحيحة',
                ], 401);
            }

            if (!$user->is_verified) {
                $this->logActivity(
                    'login_failed',
                    'محاولة تسجيل دخول غير مفعل عبر التطبيق: ' . $user->full_name,
                    $user->id
                );

                return response()->json([
                    'success' => false,
                    'needs_verification' => true,
                    'message' => 'يرجى تفعيل الحساب عبر البريد الإلكتروني',
                ], 403);
            }

            $token = $this->createTokenForDevice($user, $request);
            $this->logActivity('login', 'تسجيل دخول عبر التطبيق: ' . $user->full_name, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'token' => $token,
                'user' => $user,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تسجيل الدخول',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /*  =====================================================
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

        $token = $this->createTokenForDevice($user, $request);
        $this->logActivity('login', 'تسجيل دخول عبر التطبيق: ' . $user->full_name, $user->id);

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
        $user = $request->user();
        $this->logActivity('logout', 'تسجيل خروج عبر التطبيق: ' . $user->full_name, $user->id);
        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }

    private function createTokenForDevice(User $user, Request $request): string
    {
        $tokenName = $this->resolveTokenName($request);
        $newToken = $user->createToken($tokenName);

        $newToken->accessToken->forceFill([
            'device_name' => $request->input('device_name') ?: null,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent() ?: null,
        ])->save();

        return $newToken->plainTextToken;
    }

    private function resolveTokenName(Request $request): string
    {
        $deviceName = trim((string) $request->input('device_name'));

        if ($deviceName !== '') {
            return $deviceName;
        }

        $userAgent = (string) $request->userAgent();

        return $userAgent !== '' ? $userAgent : 'auth_token';
    }
}
