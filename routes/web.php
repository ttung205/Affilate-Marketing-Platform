<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\GoogleRegistrationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DashboardController;

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

Route::get('/admin', function(){
    return view('admin.dashboard');
})->middleware('auth', 'role:admin')->name('admin.dashboard');

Route::get('/shop', function(){
    return view('shop.dashboard');
})->middleware('auth', 'role:shop')->name('shop.dashboard');

Route::get('/publisher', function(){
    return view('publisher.dashboard');
})->middleware('auth', 'role:publisher')->name('publisher.dashboard');

Route::get('/forgot-password', [ForgotPasswordController::class,'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class,'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class,'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class,'reset'])->name('password.update');

// Admin routes
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Product management routes - CRUD đầy đủ
    Route::resource('products', ProductController::class);
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    
    // Category management routes - CRUD đầy đủ
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);
});