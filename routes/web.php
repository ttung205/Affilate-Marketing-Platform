<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home route with role-based redirection
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        // Redirect to appropriate dashboard based on role
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'publisher':
                return redirect()->route('publisher.dashboard');
            case 'shop':
                return redirect()->route('shop.dashboard');
            default:
                return redirect()->route('dashboard');
        }
    }
    return view('home');
})->name('home');

// Dashboard route with role-based redirection
Route::get('/dashboard', function () {
    $user = auth()->user();
    // Redirect to appropriate dashboard based on role
    switch ($user->role) {
        case 'admin':
            return redirect()->route('admin.dashboard');
        case 'publisher':
            return redirect()->route('publisher.dashboard');
        case 'shop':
            return redirect()->route('shop.dashboard');
        default:
            // If role doesn't match, redirect to home
            return redirect()->route('home');
    }
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Module Routes
|--------------------------------------------------------------------------
|
| Load routes from separate module files for better organization
|
*/

// Authentication routes
require __DIR__.'/modules/auth.php';

// Chatbot routes
require __DIR__.'/modules/chatbot.php';

// Tracking routes
require __DIR__.'/modules/tracking.php';

// API routes
require __DIR__.'/modules/api.php';

// Admin routes
require __DIR__.'/modules/admin.php';

// Publisher routes
require __DIR__.'/modules/publisher.php';

// Shop routes
require __DIR__.'/modules/shop.php';