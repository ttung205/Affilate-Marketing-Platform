<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GoogleAuth\GoogleController;
use App\Http\Controllers\GoogleAuth\GoogleRegistrationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\GoogleAuth\TwoFactorController;

// Basic authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Google OAuth routes
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');

// Google Registration routes
Route::get('auth/google/registration', [GoogleRegistrationController::class, 'showRegistrationForm'])->name('google.registration');
Route::post('auth/google/registration', [GoogleRegistrationController::class, 'completeRegistration'])->name('google.registration.post');

// Password reset routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Two-Factor Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/2fa/verify', [TwoFactorController::class, 'showVerify'])->name('2fa.verify');
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/2fa/setup', [TwoFactorController::class, 'showSetup'])->name('2fa.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enableTwoFactor'])->name('2fa.enable');
    Route::post('/2fa/disable', [TwoFactorController::class, 'disableTwoFactor'])->name('2fa.disable');
});
