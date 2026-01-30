<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DebtController;
use App\Http\Controllers\Api\DiscountController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\ActivationController;
use App\Http\Controllers\Api\SetupController;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/qr-login', [AuthController::class, 'loginWithToken']);
Route::post('/auth/logout-beacon', [AuthController::class, 'logoutWithBeacon']);
Route::get('/settings', [SettingController::class, 'index']);
Route::get('/print-receipt/{id}', [TransactionController::class, 'showForPrint']); // Public access for printing receipt

// Activation routes (public - must be accessible before login)
Route::get('/activation/status', [ActivationController::class, 'status']);
Route::post('/activation/activate', [ActivationController::class, 'activate']);

// Setup routes (public - must be accessible before login)
Route::prefix('setup')->group(function () {
    Route::get('/status', [SetupController::class, 'status']);
    Route::post('/create-store', [SetupController::class, 'createStore']);
    Route::post('/restore', [SetupController::class, 'restoreDatabase']);
    Route::post('/complete', [SetupController::class, 'markComplete']);
});

// Protected routes
Route::middleware(['auth:sanctum', 'activation'])->group(function () {
    // Auth routes moved to exempted group below

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Products
    Route::apiResource('products', ProductController::class);
    Route::post('/products/barcode', [ProductController::class, 'findByBarcode']);

    // Transactions
    Route::apiResource('transactions', TransactionController::class)->only(['index', 'store', 'show', 'destroy']);
    Route::post('/transactions/qris', [TransactionController::class, 'generateQris']);
    Route::post('/transactions/qris/callback', [TransactionController::class, 'qrisCallback']);

    // Reports
    Route::get('/reports/sales', [ReportController::class, 'sales']);
    Route::get('/reports/sales/pdf', [ReportController::class, 'exportPdf']);
    Route::get('/reports/sales/excel', [ReportController::class, 'exportExcel']);

    // Settings (POST for FormData file uploads, PUT for JSON updates)
    Route::put('/settings', [SettingController::class, 'update']);
    Route::post('/settings', [SettingController::class, 'update']);

    // Customers
    Route::apiResource('customers', CustomerController::class);

    // Debts
    Route::get('/debts', [DebtController::class, 'index']);
    Route::post('/debts/pay', [DebtController::class, 'store']);
    Route::get('/debts/{transaction}/history', [DebtController::class, 'show']);

    // Discounts
    Route::get('/discounts/active', [DiscountController::class, 'active']);
    Route::apiResource('discounts', DiscountController::class);

    // Users (Admin only)
    Route::apiResource('users', UserController::class);
    Route::get('/users/available/permissions', [UserController::class, 'permissions']);
    Route::post('/users/{id}/generate-qr-token', [AuthController::class, 'generateLoginToken']);
    Route::post('/users/{id}/revoke-qr-token', [AuthController::class, 'revokeLoginToken']);

    // Backup (Admin only)
    Route::prefix('backup')->group(function () {
        Route::get('/', [BackupController::class, 'index']);
        Route::post('/create', [BackupController::class, 'create']);
        Route::post('/restore', [BackupController::class, 'restore']);
        Route::post('/restore-upload', [BackupController::class, 'restoreFromUpload']);
        Route::get('/download/{filename}', [BackupController::class, 'download']);
        Route::delete('/{filename}', [BackupController::class, 'destroy']);
    });
});

// Protected routes exempted from Activation Check (so Super Admin can manage licenses)
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth (Allow identity check and logout even if license is invalid)
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Activation Management (Admin only)
    Route::get('/activations', [ActivationController::class, 'index']);
    Route::post('/activations/revoke', [ActivationController::class, 'revoke']);
});
