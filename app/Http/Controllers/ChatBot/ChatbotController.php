<?php

namespace App\Http\Controllers\ChatBot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    public function send(Request $request)
    {
        $userMessage = $request->input('message');
        $user = Auth::user();
        $userRole = $user->role ?? 'guest';
        $userName = $user->name ?? 'Khách';
        $userId = $user->id ?? null;

        // Sử dụng GeminiService để gọi API với user context
        $geminiService = new GeminiService();
        $botReply = $geminiService->ask($userMessage, $userRole, $userName, $userId);

        return response()->json([
            'user' => $userMessage,
            'bot' => $botReply,
        ]);
    }
}
