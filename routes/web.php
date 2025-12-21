<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/test-mail', function () {
    Mail::raw('اختبار إرسال الإيميل من Blood Finder', function ($msg) {
        $msg->to('alhgiabobker213@gmail.com')
            ->subject('Test Mail');
    });

    return 'Mail Sent Successfully';
});



/*
|--------------------------------------------------------------------------
| ADMIN Controllers
|-----------------------------------------------------------------------
*/
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\HospitalController;
use App\Http\Controllers\Admin\BloodStockController;
use App\Http\Controllers\Admin\BloodRequestController;
use App\Http\Controllers\Admin\DonationController;


/*
|--------------------------------------------------------------------------
| HOSPITAL Controllers
|--------------------------------------------------------------
*/
use App\Http\Controllers\Hospital\Auth\HospitalAuthController;
use App\Http\Controllers\Hospital\Dashboard\HospitalDashboardController;
use App\Http\Controllers\Hospital\HospitalRequestsController;
use App\Http\Controllers\Hospital\HospitalStockController;
use App\Http\Controllers\Hospital\HospitalAppointmentController;
use App\Http\Controllers\Hospital\HospitalProfileController;
use App\Http\Controllers\Hospital\HospitalNotificationController;
use App\Http\Controllers\Hospital\HospitalSettingsController;

/*
|--------------------------------------------------------------------------
| الصفحة الرئيسية
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect('/login'));

/*
|----------------------------------------------------------------
| تسجيل الدخول العام
|------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'loginPage'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|------------------------------------------------------------------------
| لوحة التحكم — Admin Panel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        /*
        |---------------------------------------------------------------------
        | Users
        |------------------------------------------------------------------------
        */
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');

        Route::get('/users/{id}/json', [UserController::class, 'showJson'])->name('users.json');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/users/export', [UserController::class, 'export'])->name('users.export');

        /*
        |-----------------------------------------------------------------------
        | Hospitals
        |--------------------------------------------------------------------------
        */
        Route::get('/hospitals', [HospitalController::class, 'index'])->name('hospitals.index');
        Route::post('/hospitals', [HospitalController::class, 'store'])->name('hospitals.store');
        Route::get('/hospitals/{id}/json', [HospitalController::class, 'json'])->name('hospitals.json');
        Route::put('/hospitals/{id}', [HospitalController::class, 'update'])->name('hospitals.update');
        Route::delete('/hospitals/{id}', [HospitalController::class, 'destroy'])->name('hospitals.destroy');

        /*
        |--------------------------------------------------------------------------
        | Blood Stock
        |-----------------------------------lll--------------------------------------
        */
        Route::get('/inventory', [BloodStockController::class, 'index'])->name('inventory.index');
        Route::post('/blood-stock/update', [BloodStockController::class, 'updateStock'])->name('bloodstock.updateStock');
        Route::get('/blood-stock/details/{id}', [BloodStockController::class, 'details'])->name('bloodstock.details');
        Route::delete('/blood-stock/{id}', [BloodStockController::class, 'destroy'])->name('bloodstock.destroy');

        /*
        |--------------------------------------------------------------------------
        | Blood Requests
        |--------------------------------------------------------------------------
        */
        Route::get('/blood-requests', [BloodRequestController::class, 'index'])->name('requests.index');
        Route::get('/blood-requests/{id}/json', [BloodRequestController::class, 'toJson'])->name('requests.json');
        Route::get('/blood-requests/{id}/history', [BloodRequestController::class, 'history'])->name('requests.history');

        Route::post('/blood-requests/store', [BloodRequestController::class, 'store'])->name('requests.store');
        Route::put('/blood-requests/{id}', [BloodRequestController::class, 'update'])->name('requests.update');

        Route::post('/blood-requests/{id}/status', [BloodRequestController::class, 'changeStatus'])->name('requests.status');
        Route::delete('/blood-requests/{id}', [BloodRequestController::class, 'destroy'])->name('requests.destroy');

        /*
        |--------------------------------------------------------------------------
        | Donations
        |--------------------------------------------------------------------------
        */
        Route::get('/donations', [DonationController::class, 'index'])->name('donations.index');
        Route::get('/donations/{id}', [DonationController::class, 'show'])->name('donations.show');
        Route::post('/donations/{id}/status', [DonationController::class, 'updateStatus'])->name('donations.status');
        Route::delete('/donations/{id}', [DonationController::class, 'destroy'])->name('donations.destroy');
    });

/*
|--------------------------------------------------------------------------
| لوحة تحكم المستشفى — Hospital Panel
|--------------------------------------------------------------------------
*/
Route::prefix('hospital')
    ->name('hospital.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Registration Steps (مراحل التسجيل)
        |--------------------------------------------------------------------------
        */
        Route::get('/register/step1', [HospitalAuthController::class, 'registerStep1'])->name('register.step1');
        Route::post('/register/step1', [HospitalAuthController::class, 'registerStep1Post'])->name('register.step1.post');

        Route::get('/register/step2', [HospitalAuthController::class, 'registerStep2'])->name('register.step2');
        Route::post('/register/step2', [HospitalAuthController::class, 'registerStep2Post'])->name('register.step2.post');

        Route::get('/pending', [HospitalAuthController::class, 'pending'])->name('pending');
        Route::post('/check-status', [HospitalAuthController::class, 'checkStatus'])->name('checkStatus');

        Route::post('/logout', [HospitalAuthController::class, 'logout'])->name('logout');

        /*
        |--------------------------------------------------------------------------
        | Protected Hospital Panel
        |--------------------------------------------------------------------------
        */
        Route::middleware('auth')->group(function () {

            // Dashboard
            Route::get('/dashboard', [HospitalDashboardController::class, 'index'])->name('dashboard');

            /*
            |--------------------------------------------------------------------------
            | Blood Requests
            |--------------------------------------------------------------------------
            */
            Route::get('/requests', [HospitalRequestsController::class, 'index'])->name('requests.index');
            Route::get('/requests/show/{id}', [HospitalRequestsController::class, 'showJson'])->name('requests.show');
            Route::post('/requests/{id}/status', [HospitalRequestsController::class, 'updateStatus'])
                ->name('requests.update-status');
    Route::post(
    '/requests/{id}/patient-info',
    [HospitalRequestsController::class, 'savePatientInfo']
)->name('requests.patient-info');

Route::post(
    '/requests/store',
    [HospitalRequestsController::class, 'store']
)->name('requests.store');



            /*
            |--------------------------------------------------------------------------
            | Blood Stock
            |--------------------------------------------------------------------------
            */
            Route::get('/inventory', [HospitalStockController::class, 'index'])->name('inventory.index');
            Route::post('/inventory/update', [HospitalStockController::class, 'update'])->name('inventory.update');

            /*
            |--------------------------------------------------------------------------
            | Appointments
            |--------------------------------------------------------------------------
            */
            Route::get('/appointments', [HospitalAppointmentController::class, 'index'])->name('appointments.index');
            Route::post('/appointments/update-status', [HospitalAppointmentController::class, 'updateStatus'])
                ->name('appointments.updateStatus');
            Route::get('/appointments/{id}/json', [HospitalAppointmentController::class, 'showJson'])
                ->name('appointments.json');

            /*
            |--------------------------------------------------------------------------
            | Profile (Abubaker )
            |--------------------------------------------------------------------------
            */
            Route::get('/profile', [HospitalProfileController::class, 'index'])->name('profile.index');

            Route::put('/profile/update-hospital', [HospitalProfileController::class, 'updateHospital'])
                ->name('profile.update');

            Route::put('/profile/update-credentials', [HospitalProfileController::class, 'updateCredentials'])
                ->name('profile.credentials');

            Route::put('/profile/update-password', [HospitalProfileController::class, 'updatePassword'])
                ->name('profile.password');

            Route::post('/profile/check-password', [HospitalProfileController::class, 'checkPassword'])
                ->name('profile.checkPassword');
            /*
            |--------------------------------------------------------------------------
            | Notifications
            |--------------------------------------------------------------------------
            */
            Route::get('/notifications', [HospitalNotificationController::class, 'index'])
                ->name('notifications.index');

            Route::get('/notifications/{id}', [HospitalNotificationController::class, 'show'])
                ->name('notifications.show');

            Route::post('/notifications/mark-all-read', [HospitalNotificationController::class, 'markAllRead'])
                ->name('notifications.markAllRead');

            /*
            |--------------------------------------------------------------------------
            | Settings
            |--------------------------------------------------------------------------
            */


            Route::get('/settings', [HospitalSettingsController::class, 'index'])->name('settings.index');
            Route::put('/settings', [HospitalSettingsController::class, 'update'])->name('settings.update');
        });
    });
