<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\GoogleRegistrationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AffiliateLinkController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\TrackingController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/login' , [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register' , [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

// Google OAuth routes
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');

// Google Registration routes
Route::get('auth/google/registration', [GoogleRegistrationController::class, 'showRegistrationForm'])->name('google.registration');
Route::post('auth/google/registration', [GoogleRegistrationController::class, 'completeRegistration'])->name('google.registration.post');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Affiliate tracking routes
Route::get('/track/{trackingCode}', [TrackingController::class, 'redirectByTrackingCode'])->name('tracking.track');
Route::get('/ref/{shortCode}', [TrackingController::class, 'redirectByShortCode'])->name('tracking.short');


Route::get('/admin', function(){
    return view('admin.dashboard');
})->middleware('auth', 'role:admin')->name('admin.dashboard');

// Shop routes
Route::middleware(['auth', 'role:shop'])->prefix('shop')->name('shop.')->group(function () {
    Route::get('/', [App\Http\Controllers\Shop\DashboardController::class, 'dashboard'])->name('dashboard');
    
    // Product management routes
    Route::resource('products', App\Http\Controllers\Shop\ProductController::class);
    Route::patch('/products/{product}/toggle-status', [App\Http\Controllers\Shop\ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::delete('/products/{product}/image', [App\Http\Controllers\Shop\ProductController::class, 'removeImage'])->name('products.remove-image');
    
    // Order management routes
    Route::resource('orders', App\Http\Controllers\Shop\OrderController::class);
    
    // Revenue routes
    Route::get('/revenue', [App\Http\Controllers\Shop\RevenueController::class, 'index'])->name('revenue.index');
    
    // Payment routes
    Route::get('/payments', [App\Http\Controllers\Shop\PaymentController::class, 'index'])->name('payments.index');
    
    // Settings routes
    Route::get('/settings', [App\Http\Controllers\Shop\SettingController::class, 'index'])->name('settings.index');
    
    // Profile routes
    Route::get('/profile/edit', [App\Http\Controllers\Shop\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Shop\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/avatar', [App\Http\Controllers\Shop\ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::delete('/profile/avatar', [App\Http\Controllers\Shop\ProfileController::class, 'removeAvatar'])->name('profile.remove-avatar');
});

// Publisher routes
Route::middleware(['auth', 'role:publisher'])->prefix('publisher')->name('publisher.')->group(function () {
    Route::get('/', [App\Http\Controllers\PublisherController::class, 'dashboard'])->name('dashboard');
    
    // Affiliate Links management routes
    Route::resource('affiliate-links', App\Http\Controllers\Publisher\AffiliateLinkController::class);
    
    // Campaigns routes
    Route::resource('campaigns', App\Http\Controllers\Publisher\CampaignController::class);
    
    // Products routes
    Route::resource('products', App\Http\Controllers\Publisher\ProductController::class);
    Route::post('/products/{product}/affiliate-link', [App\Http\Controllers\Publisher\ProductController::class, 'createAffiliateLink'])->name('products.create-affiliate-link');
    
    // Reports routes
    Route::get('/reports/performance', [App\Http\Controllers\Publisher\ReportController::class, 'performance'])->name('reports.performance');
    Route::get('/reports/commissions', [App\Http\Controllers\Publisher\ReportController::class, 'commissions'])->name('reports.commissions');
    Route::get('/reports/clicks', [App\Http\Controllers\Publisher\ReportController::class, 'clicks'])->name('reports.clicks');
    
    // Payments routes
    Route::resource('payments', App\Http\Controllers\Publisher\PaymentController::class);
    
    // Settings routes
    Route::resource('settings', App\Http\Controllers\Publisher\SettingController::class);
    
    // Profile routes
    Route::get('/profile/edit', [App\Http\Controllers\Publisher\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Publisher\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/avatar', [App\Http\Controllers\Publisher\ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::delete('/profile/avatar', [App\Http\Controllers\Publisher\ProfileController::class, 'removeAvatar'])->name('profile.remove-avatar');
});

Route::get('/forgot-password', [ForgotPasswordController::class,'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class,'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class,'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class,'reset'])->name('password.update');

// Notification API routes
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'destroy']);
});


// Admin routes
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
    Route::get('/notifications', [App\Http\Controllers\Admin\NotificationManagementController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send-all', [App\Http\Controllers\Admin\NotificationManagementController::class, 'sendToAll'])->name('notifications.send-all');
    Route::post('/notifications/send-role', [App\Http\Controllers\Admin\NotificationManagementController::class, 'sendToRole'])->name('notifications.send-role');
    Route::post('/notifications/send-user', [App\Http\Controllers\Admin\NotificationManagementController::class, 'sendToUser'])->name('notifications.send-user');
    Route::get('/notifications/users', [App\Http\Controllers\Admin\NotificationManagementController::class, 'getUsersByRole'])->name('notifications.users');
    Route::get('/notifications/stats', [App\Http\Controllers\Admin\NotificationManagementController::class, 'getStats'])->name('notifications.stats');
});
