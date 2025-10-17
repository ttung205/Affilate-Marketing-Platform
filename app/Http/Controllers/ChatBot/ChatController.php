<?php

namespace App\Http\Controllers\ChatBot;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Send a message and get bot response
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string|max:100'
        ]);

        $user = Auth::user();
        $sessionId = $request->input('session_id') ?: $this->generateSessionId();
        $userMessage = $request->input('message');
        $ipAddress = $request->ip();

        // Save user message to database
        $userChatMessage = ChatMessage::create([
            'user_id' => $user?->id,
            'session_id' => $sessionId,
            'type' => 'user',
            'message' => $userMessage,
            'ip_address' => $ipAddress,
            'metadata' => [
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString()
            ]
        ]);

        try {
            // Get response from Gemini
            $botResponse = $this->geminiService->ask($userMessage, $user?->role ?? 'guest', $user?->name ?? 'Khách', $user?->id);

            // Save bot response to database
            $botChatMessage = ChatMessage::create([
                'user_id' => $user?->id,
                'session_id' => $sessionId,
                'type' => 'bot',
                'message' => $botResponse,
                'ip_address' => $ipAddress,
                'metadata' => [
                    'model' => 'gemini',
                    'timestamp' => now()->toISOString()
                ]
            ]);

            return response()->json([
                'success' => true,
                'bot' => $botResponse,
                'session_id' => $sessionId,
                'user_message_id' => $userChatMessage->id,
                'bot_message_id' => $botChatMessage->id
            ]);

        } catch (\Exception $e) {
            // Log error but still save user message
            \Log::error('Chat API Error: ' . $e->getMessage());

            // Fallback response
            $fallbackResponse = $this->getFallbackResponse($user);

            $botChatMessage = ChatMessage::create([
                'user_id' => $user?->id,
                'session_id' => $sessionId,
                'type' => 'bot',
                'message' => $fallbackResponse,
                'ip_address' => $ipAddress,
                'metadata' => [
                    'error' => $e->getMessage(),
                    'fallback' => true,
                    'timestamp' => now()->toISOString()
                ]
            ]);

            return response()->json([
                'success' => false,
                'bot' => $fallbackResponse,
                'session_id' => $sessionId,
                'error' => 'Service temporarily unavailable'
            ]);
        }
    }

    /**
     * Get conversation history for a session
     */
    public function getConversationHistory(Request $request): JsonResponse
    {
        $sessionId = $request->input('session_id');

        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'Session ID is required'
            ], 400);
        }

        $messages = ChatMessage::getConversationHistory($sessionId);

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'session_id' => $sessionId
        ]);
    }

    /**
     * Generate a unique session ID
     */
    private function generateSessionId(): string
    {
        return 'chat_' . Str::random(32) . '_' . time();
    }


    /**
     * Get fallback response when Gemini service fails
     */
    private function getFallbackResponse($user): string
    {
        $responses = [
            "Xin lỗi, tôi đang gặp sự cố kỹ thuật. Vui lòng thử lại sau.",
            "Dịch vụ tạm thời không khả dụng. Bạn có thể liên hệ hỗ trợ trực tiếp.",
            "Tôi đang bận xử lý. Hãy thử lại trong vài phút."
        ];

        return $responses[array_rand($responses)];
    }
}
