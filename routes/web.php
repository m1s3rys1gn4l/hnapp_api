<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\Admin\AdminPaymentRequestController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');

    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/plans', [AdminPlanController::class, 'index'])->name('plans.index');
        Route::get('/plans/create', [AdminPlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [AdminPlanController::class, 'store'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [AdminPlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [AdminPlanController::class, 'update'])->name('plans.update');
        Route::delete('/plans/{plan}', [AdminPlanController::class, 'destroy'])->name('plans.destroy');

        Route::get('/payment-requests', [AdminPaymentRequestController::class, 'index'])->name('payment-requests.index');
        Route::post('/payment-requests/{paymentRequest}/approve', [AdminPaymentRequestController::class, 'approve'])->name('payment-requests.approve');
        Route::post('/payment-requests/{paymentRequest}/reject', [AdminPaymentRequestController::class, 'reject'])->name('payment-requests.reject');

        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/send-password-reset', [AdminUserController::class, 'sendPasswordReset'])->name('users.send-password-reset');
        Route::delete('/users/bulk-delete', [AdminUserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::get('/settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
        Route::put('/settings/email', [AdminSettingsController::class, 'updateEmailSettings'])->name('settings.update-email');
        Route::put('/settings/sms', [AdminSettingsController::class, 'updateSmsSettings'])->name('settings.update-sms');
        Route::post('/settings/sms/test', [AdminSettingsController::class, 'testSms'])->name('settings.test-sms');
    });
});
