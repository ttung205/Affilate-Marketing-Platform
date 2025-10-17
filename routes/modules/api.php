<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Publisher\ConversionController;
use App\Http\Controllers\Notification\NotificationController;

// Conversion webhook routes
Route::post('/api/publisher/conversion/create', [ConversionController::class, 'create'])->name('conversion.create');
Route::middleware(['auth', 'role:publisher'])->group(function () {
    Route::get('/api/conversions', [ConversionController::class, 'getPublisherConversions'])->name('conversions.list');
    Route::get('/api/conversions/stats', [ConversionController::class, 'getPublisherStats'])->name('conversions.stats');
});

// Notification API routes
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});
