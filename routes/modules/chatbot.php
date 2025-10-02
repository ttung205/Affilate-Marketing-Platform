<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;

// Chatbot demo route
Route::get('/chatbot/demo', function () {
    return view('chatbot.demo');
})->name('chatbot.demo');

// Chatbot route
Route::post('/chat/send', [ChatbotController::class, 'send'])->name('chat.send');
