<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Controllers Import
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomePageController;
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Api\BloodRequestController;
use App\Services\FCMService;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Public Routes (No Auth)
|--------------------------------------------------------------------------
*/

// =======================
// 🔐 Authentication
// =======================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 🔐 Email OTP
Route::post('/verify-email-otp', [AuthController::class, 'verifyEmailOtp']);
Route::post('/resend-email-otp', [AuthController::class, 'resendEmailOtp']);

// Social Login
Route::post('/google-login', [AuthController::class, 'googleLogin']);

// =======================
// 🏥 Hospitals
// =======================
Route::get('/hospitals', [HospitalController::class, 'index']);

// =======================
// 📅 Appointment (Requires Auth)
// =======================
Route::post('/appointments/create', [AppointmentController::class, 'store'])
    ->middleware('auth:sanctum');


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


    // Send Notifications
    Route::post('/notify/user', [NotificationController::class, 'sendToUser']);
    Route::post('/notify/all', [NotificationController::class, 'sendToAll']);

    // Save FCM Token
   Route::post('/save-fcm-token', [NotificationController::class, 'saveToken']); 

   // 
   Route::get('/blood-requests', [BloodRequestController::class, 'index']);
    Route::post('/blood-requests', [BloodRequestController::class, 'store']);
    Route::get('/blood-requests/{id}', [BloodRequestController::class, 'show']);
    Route::post('/blood-requests/{id}/cancel', [BloodRequestController::class, 'cancel']);
});


