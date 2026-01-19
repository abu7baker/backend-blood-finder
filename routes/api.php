<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomePageController;
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\BloodRequestController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication)
|--------------------------------------------------------------------------
*/

// =======================
// 🔐 Authentication
// =======================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Email OTP
Route::post('/verify-email-otp', [AuthController::class, 'verifyEmailOtp']);
Route::post('/resend-email-otp', [AuthController::class, 'resendEmailOtp']);

// Google Login
Route::post('/google-login', [AuthController::class, 'googleLogin']);

// =======================
// 🏥 Hospitals (Public)
// =======================
Route::get('/hospitals', [HospitalController::class, 'index']);

Route::middleware('auth:sanctum')->post(
    '/login/biometric',
    [AuthController::class, 'biometricLogin']
);
Route::middleware(auth()::class)->post(
    '/enable-biometric',
    [AuthController::class, 'enableBiometric']
);

/*
|--------------------------------------------------------------------------
| Protected Routes (auth:sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // =======================
    // 👤 User
    // =======================
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/delete-account', [AuthController::class, 'deleteAccount']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // =======================
    // 🏠 Home
    // =======================
    Route::get('/home', [HomePageController::class, 'index']);
    Route::post('/toggle-donation', [HomePageController::class, 'toggleDonation']);

    // =======================
    // 🔔 Notifications
    // =======================
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);

    // Save FCM Token
    Route::post('/save-fcm-token', [NotificationController::class, 'saveToken']);

    // =======================
    // 📅 Appointments
    // =======================
    Route::post('/appointments/create', [AppointmentController::class, 'store']);

    // =======================
    // 🩸 Blood Requests
    // =======================

    // 👤 المستخدم
    Route::get('/blood-requests', [BloodRequestController::class, 'index']);              // طلباتي
    Route::post('/blood-requests', [BloodRequestController::class, 'store']);             // إنشاء طلب
    Route::get('/blood-requests/{id}', [BloodRequestController::class, 'show']);          // تفاصيل طلب
    Route::post('/blood-requests/{id}/cancel', [BloodRequestController::class, 'cancel']); // إلغاء

    // 🧑‍🦰 رد المتبرع (✔ هذا هو المسار الصحيح)
    Route::post(
        '/blood-requests/{id}/respond',
        [BloodRequestController::class, 'respondToRequest']
    );

    // 🏥 المستشفى (تغيير حالة الطلب)
    Route::post(
        '/blood-requests/{id}/change-status',
        [BloodRequestController::class, 'changeStatus']
    );

});
