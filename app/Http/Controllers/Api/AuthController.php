<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use Throwable;

class AuthController extends Controller
{
    use LogsActivity;

    /* =====================================================
     |  تسجيل مستخدم جديد (إرسال OTP فقط – بدون إنشاء حساب)
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

            // ✅ تنظيف القيم الفارغة
            foreach ($data as $key => $value) {
                if ($value === '') {
                    $data[$key] = null;
                }
            }

            // 🔐 إنشاء OTP
            $otp = random_int(100000, 999999);

            // ⏳ تخزين بيانات التسجيل مؤقتًا (10 دقائق)
            Cache::put(
                'register_' . $data['email'],
                [
                    'data' => $data,
                    'otp' => (string) $otp,
                    'expires_at' => now()->addMinutes(10),
                ],
                now()->addMinutes(10)
            );

            // ✉️ إرسال رمز التحقق
            Mail::raw(
                "مرحباً {$data['full_name']}\n\nرمز التحقق: {$otp}\n\nالرمز صالح لمدة 10 دقائق.",
                function ($message) use ($data) {
                    $message->to($data['email'])
                        ->subject('رمز التحقق من البريد الإلكتروني');
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رمز التحقق إلى البريد الإلكتروني',
                'needs_verification' => true,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'type' => 'validation_error',
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors(),
            ], 422);

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
     |  التحقق من OTP (هنا يتم إنشاء الحساب فعليًا)
     ===================================================== */
    public function verifyEmailOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|string',
            ]);

            $cached = Cache::get('register_' . $request->email);

            if (!$cached) {
                return response()->json([
                    'success' => false,
                    'message' => 'انتهت صلاحية رمز التحقق',
                ], 422);
            }

            if ((string) $cached['otp'] !== (string) $request->otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق غير صحيح',
                ], 422);
            }

            $data = $cached['data'];

            // ✅ إنشاء المستخدم بعد التحقق فقط
            $user = User::create([
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'age' => $data['age'],
                'gender' => $data['gender'],
                'city' => $data['city'],
                'blood_type' => $data['blood_type'],
                'chronic_disease' => $data['chronic_disease'],
                'emergency_phone' => $data['emergency_phone'],
                'password' => Hash::make($data['password']),
                'role_id' => 3,
                'donation_eligibility' => 'eligible',
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);

            Cache::forget('register_' . $request->email);

            $token = $this->createTokenForDevice($user, $request);
            $this->logActivity('login', 'تسجيل حساب جديد وتحقق عبر الإيميل: ' . $user->full_name, $user->id);

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
 |  إعادة إرسال OTP
 ===================================================== */
    public function resendEmailOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $cached = Cache::get('register_' . $request->email);

            if (!$cached) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد طلب تسجيل أو انتهت صلاحية الرمز',
                ], 422);
            }

            // إنشاء OTP جديد
            $otp = rand(100000, 999999);

            // تحديث الكاش
            Cache::put(
                'register_' . $request->email,
                [
                    'data' => $cached['data'],
                    'otp' => $otp,
                    'expires_at' => now()->addMinutes(10),
                ],
                now()->addMinutes(10)
            );

            // إرسال الإيميل
            Mail::raw(
                "مرحباً {$cached['data']['full_name']}\n\nرمز التحقق الجديد: {$otp}\n\nالرمز صالح لمدة 10 دقائق.",
                function ($message) use ($request) {
                    $message->to($request->email)
                        ->subject('إعادة إرسال رمز التحقق');
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رمز تحقق جديد إلى البريد الإلكتروني',
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ أثناء إعادة إرسال الرمز',
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
                    'محاولة تسجيل دخول فاشلة: ' . $request->phone,
                    $user?->id
                );

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

            $token = $this->createTokenForDevice($user, $request);
            $this->logActivity('login', 'تسجيل دخول: ' . $user->full_name, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح',
                'token' => $token,
                'user' => $user,
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تسجيل الدخول',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /* =====================================================
     |  تسجيل الدخول عبر Google (كما هو)
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
        $this->logActivity('login', 'تسجيل دخول عبر Google: ' . $user->full_name, $user->id);

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user->fresh(),
            'needs_completion' => $needsCompletion,
        ]);
    }

    /* =====================================================
     |  تحديث بيانات المستخدم
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
        $this->logActivity('logout', 'تسجيل خروج: ' . $user->full_name, $user->id);
        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }

    /* =====================================================
     |  Helpers
     ===================================================== */
    private function createTokenForDevice(User $user, Request $request): string
    {
        $tokenName = $this->resolveTokenName($request);
        $newToken = $user->createToken($tokenName);

        $newToken->accessToken->forceFill([
            'device_name' => $request->input('device_name') ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?: null,
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
