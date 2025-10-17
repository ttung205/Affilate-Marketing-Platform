<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tracking\TrackingController;

// Affiliate tracking routes
Route::get('/track/{trackingCode}', [TrackingController::class, 'redirectByTrackingCode'])->name('tracking.track');
Route::get('/ref/{shortCode}', [TrackingController::class, 'redirectByShortCode'])->name('tracking.short');
