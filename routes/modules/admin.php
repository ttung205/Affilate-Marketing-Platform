<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\AffiliateLinkController;
use App\Http\Controllers\Admin\WithdrawalApprovalController;
use App\Http\Controllers\Admin\NotificationManagementController;
use App\Http\Controllers\Admin\PlatformFeeController;
use App\Http\Controllers\Admin\PlatformFeePaymentController;
use App\Http\Controllers\Admin\FraudDetectionController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // Product Management
    Route::resource('products', ProductController::class);
    
    // Category Management
    Route::resource('categories', CategoryController::class);
    
    // Campaign Management
    Route::resource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/toggle-status', [CampaignController::class, 'toggleStatus'])->name('campaigns.toggle-status');
    
    // Affiliate Links
    Route::get('affiliate-links', [AffiliateLinkController::class, 'index'])->name('affiliate-links.index');
    Route::get('affiliate-links/create', [AffiliateLinkController::class, 'create'])->name('affiliate-links.create');
    Route::post('affiliate-links', [AffiliateLinkController::class, 'store'])->name('affiliate-links.store');
    Route::get('affiliate-links/{affiliateLink}', [AffiliateLinkController::class, 'show'])->name('affiliate-links.show');
    Route::get('affiliate-links/{affiliateLink}/edit', [AffiliateLinkController::class, 'edit'])->name('affiliate-links.edit');
    Route::put('affiliate-links/{affiliateLink}', [AffiliateLinkController::class, 'update'])->name('affiliate-links.update');
    Route::delete('affiliate-links/{affiliateLink}', [AffiliateLinkController::class, 'destroy'])->name('affiliate-links.destroy');
    Route::post('affiliate-links/{affiliateLink}/toggle-status', [AffiliateLinkController::class, 'toggleStatus'])->name('affiliate-links.toggle-status');
    
    // Withdrawal Approvals
    Route::get('withdrawals', [WithdrawalApprovalController::class, 'index'])->name('withdrawals.index');
    
    // Withdrawal API routes
    Route::get('withdrawals/api/list', [WithdrawalApprovalController::class, 'getWithdrawals'])->name('withdrawals.api.list');
    Route::get('withdrawals/api/stats', [WithdrawalApprovalController::class, 'getStats'])->name('withdrawals.api.stats');
    Route::get('withdrawals/api/{withdrawal}', [WithdrawalApprovalController::class, 'getWithdrawal'])->name('withdrawals.api.show');
    Route::get('withdrawals/api/{withdrawal}/qr-code', [WithdrawalApprovalController::class, 'generateQRCode'])->name('withdrawals.api.qr-code');
    Route::post('withdrawals/api/{withdrawal}/approve', [WithdrawalApprovalController::class, 'approveWithdrawal'])->name('withdrawals.api.approve');
    Route::post('withdrawals/api/{withdrawal}/reject', [WithdrawalApprovalController::class, 'rejectWithdrawal'])->name('withdrawals.api.reject');
    Route::post('withdrawals/api/{withdrawal}/complete', [WithdrawalApprovalController::class, 'completeWithdrawal'])->name('withdrawals.api.complete');
    Route::post('withdrawals/bulk-action', [WithdrawalApprovalController::class, 'bulkAction'])->name('withdrawals.bulk-action');
    
    // Withdrawal web routes
    Route::get('withdrawals/{withdrawal}', [WithdrawalApprovalController::class, 'show'])->name('withdrawals.show');
    Route::post('withdrawals/{withdrawal}/send-otp', [WithdrawalApprovalController::class, 'sendOtp'])->name('withdrawals.send-otp');
    Route::post('withdrawals/{withdrawal}/approve', [WithdrawalApprovalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('withdrawals/{withdrawal}/reject', [WithdrawalApprovalController::class, 'reject'])->name('withdrawals.reject');
    
    // Notification Management
    Route::get('notifications/manage', [NotificationManagementController::class, 'index'])->name('notifications.manage');
    Route::get('notifications/templates', [NotificationManagementController::class, 'templates'])->name('notifications.templates');
    Route::post('notifications/send', [NotificationManagementController::class, 'send'])->name('notifications.send');
    
    // Platform Fee Settings
    Route::get('platform-fees', [PlatformFeeController::class, 'index'])->name('platform-fees.index');
    Route::post('platform-fees', [PlatformFeeController::class, 'store'])->name('platform-fees.store');
    Route::patch('platform-fees/{platformFeeSetting}', [PlatformFeeController::class, 'update'])->name('platform-fees.update');
    Route::delete('platform-fees/{platformFeeSetting}', [PlatformFeeController::class, 'destroy'])->name('platform-fees.destroy');
    
    // Platform Fee Payments
    Route::get('platform-fee-payments', [PlatformFeePaymentController::class, 'index'])->name('platform-fee-payments.index');
    Route::get('platform-fee-payments/{payment}', [PlatformFeePaymentController::class, 'show'])->name('platform-fee-payments.show');
    Route::post('platform-fee-payments/{payment}/verify', [PlatformFeePaymentController::class, 'verify'])->name('platform-fee-payments.verify');
    Route::post('platform-fee-payments/{payment}/reject', [PlatformFeePaymentController::class, 'reject'])->name('platform-fee-payments.reject');

    // Fraud Detection
    Route::prefix('fraud-detection')->name('fraud-detection.')->group(function () {
        Route::get('/', [FraudDetectionController::class, 'index'])->name('index');
        Route::get('/{id}', [FraudDetectionController::class, 'show'])->name('show');
        Route::post('/block-ip', [FraudDetectionController::class, 'blockIp'])->name('block-ip');
        Route::post('/unblock-ip', [FraudDetectionController::class, 'unblockIp'])->name('unblock-ip');
        Route::post('/clear-cache', [FraudDetectionController::class, 'clearCache'])->name('clear-cache');
        Route::get('/export', [FraudDetectionController::class, 'export'])->name('export');
        Route::post('/cleanup', [FraudDetectionController::class, 'cleanup'])->name('cleanup');
    });
});
