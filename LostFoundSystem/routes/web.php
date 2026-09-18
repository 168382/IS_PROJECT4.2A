<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ─── Public Pages ──────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
});

// ─── Authentication ────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password/{token}', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');

// ─── Item Reporting & Search (auth required) ──────────────────
Route::middleware(['auth.custom'])->group(function () {

    // Search
    Route::get('/search', [ItemController::class, 'search'])->name('search');

    // Report items (auth required so we know who's reporting)
    Route::get('/report/lost', [ItemController::class, 'showReportLostForm'])->name('report.lost');
    Route::post('/report/lost', [ItemController::class, 'storeLostItem']);

    Route::get('/report/found', [ItemController::class, 'showReportFoundForm'])->name('report.found');
    Route::post('/report/found', [ItemController::class, 'storeFoundItem']);

    // ─── User Dashboard ───────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ─── Claims (user submits, ADMIN approves) ─────────────────
    Route::post('/claims/submit', [ClaimController::class, 'submit'])->name('claims.submit');

    // Notifications
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/notifications/{id}', [NotificationController::class, 'open'])->name('notifications.open');

    // ─── Admin / Staff Only ────────────────────────────────────
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/claims', [AdminController::class, 'claims'])->name('claims');
        Route::post('/claims/{id}/approve', [AdminController::class, 'approveClaim'])->name('claims.approve');
        Route::post('/claims/{id}/reject', [AdminController::class, 'rejectClaim'])->name('claims.reject');

        Route::get('/items', [AdminController::class, 'items'])->name('items');
        Route::delete('/items/lost/{id}', [AdminController::class, 'deleteLostItem'])->name('items.lost.delete');
        Route::delete('/items/found/{id}', [AdminController::class, 'deleteFoundItem'])->name('items.found.delete');

        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users/{id}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
        Route::get('/reports/pdf', [AdminController::class, 'downloadPdfReport'])->name('reports.pdf');
        Route::get('/reports/excel', [AdminController::class, 'downloadExcelReport'])->name('reports.excel');
    });
});
