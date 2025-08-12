<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\GoogleRegistrationController;

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

Route::get('/logout', [AuthController::class, 'logout']);

Route::get('/admin', function(){
    return view('admin.dashboard');
})->middleware('auth', 'role:admin')->name('admin.dashboard');

Route::get('/shop', function(){
    return view('shop.dashboard');
})->middleware('auth', 'role:shop')->name('shop.dashboard');

Route::get('/publisher', function(){
    return view('publisher.dashboard');
})->middleware('auth', 'role:publisher')->name('publisher.dashboard');
