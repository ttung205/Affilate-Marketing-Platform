<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

// Chatbot demo route
Route::get('/chatbot/demo', function () {
    return view('chatbot.demo');
})->name('chatbot.demo');

// Chat API routes
Route::middleware('auth')->group(function () {
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/history', [ChatController::class, 'getConversationHistory'])->name('chat.history');
});

// Public chat route (for guests)
Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send.public');
