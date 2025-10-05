<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AffiliateLinkController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\NotificationManagementController;
use App\Http\Controllers\Admin\WithdrawalApprovalController;

// Admin dashboard route
Route::get('/admin', function () {
    return view('admin.dashboard');
})->middleware('auth', 'role:admin')->name('admin.dashboard');

// Admin routes group
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // User management routes
    Route::resource('users', UserController::class);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('/users/{user}/avatar', [UserController::class, 'removeAvatar'])->name('users.remove-avatar');

    // Product management routes - CRUD đầy đủ
    Route::resource('products', ProductController::class);
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::delete('/products/{product}/image', [ProductController::class, 'removeImage'])->name('products.remove-image');

    // Category management routes
    Route::resource('categories', CategoryController::class);
    Route::delete('/categories/{category}/image', [CategoryController::class, 'removeImage'])->name('categories.remove-image');

    // Affiliate Link management routes - CRUD đầy đủ
    Route::resource('affiliate-links', AffiliateLinkController::class);
    Route::patch('/affiliate-links/{affiliateLink}/toggle-status', [AffiliateLinkController::class, 'toggleStatus'])->name('affiliate-links.toggle-status');

    // Campaign management routes - CRUD đầy đủ
    Route::resource('campaigns', CampaignController::class);
    Route::patch('/campaigns/{campaign}/toggle-status', [CampaignController::class, 'toggleStatus'])->name('campaigns.toggle-status');

    // Notification management routes
    Route::get('/notifications', [NotificationManagementController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send-all', [NotificationManagementController::class, 'sendToAll'])->name('notifications.send-all');
    Route::post('/notifications/send-role', [NotificationManagementController::class, 'sendToRole'])->name('notifications.send-role');
    Route::post('/notifications/send-user', [NotificationManagementController::class, 'sendToUser'])->name('notifications.send-user');
    Route::get('/notifications/users', [NotificationManagementController::class, 'getUsersByRole'])->name('notifications.users');
    Route::get('/notifications/stats', [NotificationManagementController::class, 'getStats'])->name('notifications.stats');

    // Withdrawal approval routes
    Route::get('/withdrawals', [WithdrawalApprovalController::class, 'index'])->name('withdrawals.index');
    Route::get('/withdrawals/{withdrawal}', [WithdrawalApprovalController::class, 'show'])->name('withdrawals.show');
    Route::post('/withdrawals/{withdrawal}/approve', [WithdrawalApprovalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('/withdrawals/{withdrawal}/reject', [WithdrawalApprovalController::class, 'reject'])->name('withdrawals.reject');
    Route::post('/withdrawals/{withdrawal}/complete', [WithdrawalApprovalController::class, 'complete'])->name('withdrawals.complete');
    Route::post('/withdrawals/bulk-action', [WithdrawalApprovalController::class, 'bulkAction'])->name('withdrawals.bulk-action');

    // Withdrawal API routes
    Route::get('/withdrawals/api/list', [WithdrawalApprovalController::class, 'getWithdrawals'])->name('withdrawals.api.list');
    Route::get('/withdrawals/api/stats', [WithdrawalApprovalController::class, 'getStats'])->name('withdrawals.api.stats');
    Route::get('/withdrawals/api/{withdrawal}', [WithdrawalApprovalController::class, 'getWithdrawal'])->name('withdrawals.api.show');
    Route::post('/withdrawals/api/{withdrawal}/approve', [WithdrawalApprovalController::class, 'approveWithdrawal'])->name('withdrawals.api.approve');
    Route::post('/withdrawals/api/{withdrawal}/reject', [WithdrawalApprovalController::class, 'rejectWithdrawal'])->name('withdrawals.api.reject');
    Route::post('/withdrawals/api/{withdrawal}/complete', [WithdrawalApprovalController::class, 'completeWithdrawal'])->name('withdrawals.api.complete');
    Route::get('/withdrawals/api/{withdrawal}/qr-code', [WithdrawalApprovalController::class, 'generateQRCode'])->name('withdrawals.api.qr-code');
});
