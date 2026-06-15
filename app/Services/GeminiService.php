<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\SystemInfoService;

class GeminiService
{
    protected string $baseUrl = "https://generativelanguage.googleapis.com/v1beta/models";
    protected string $model = "gemini-2.5-flash";
    protected ?string $apiKey = null;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY') ?: '';
    }

    public function ask(string $message, string $userRole = 'guest', string $userName = 'Khách', $userId = null)
    {
        // Debug: Log API key
        Log::info('GeminiService Debug:', [
            'api_key_exists' => !empty($this->apiKey),
            'api_key_length' => strlen($this->apiKey ?? ''),
            'api_key_start' => substr($this->apiKey ?? '', 0, 10),
            'message' => $message,
            'user_role' => $userRole,
            'user_name' => $userName,
            'user_id' => $userId
        ]);

        // Kiểm tra API key
        if (!$this->apiKey || $this->apiKey === 'your_gemini_api_key_here') {
            Log::info('Using fallback: API key not configured');
            return $this->getFallbackResponse($message);
        }

        $url = "{$this->baseUrl}/{$this->model}:generateContent";

        // Tạo system context với user info và system stats
        $systemContext = $this->getSystemContext($userRole, $userName, $userId);
        $fullMessage = $systemContext . "\n\nUser: " . $message;

        $requestData = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $fullMessage]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "topK" => 40,
                "topP" => 0.95,
                "maxOutputTokens" => 1024,
            ]
        ];

        Log::info('Gemini API Request:', [
            'url' => $url,
            'headers' => [
                'x-goog-api-key' => substr($this->apiKey, 0, 10) . '...',
                'Content-Type' => 'application/json'
            ],
            'data' => $requestData
        ]);

        $response = Http::withHeaders([
            'x-goog-api-key' => $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($url, $requestData);

        Log::info('Gemini API Response:', [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'body' => $response->body()
        ]);

        if ($response->successful()) {
            $json = $response->json();
            $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if ($text) {
                Log::info('Gemini API Success:', ['response' => $text]);
                return $text;
            } else {
                Log::warning('Gemini API: No text in response', ['json' => $json]);
                return $this->getFallbackResponse($message);
            }
        }

        // Nếu API lỗi, sử dụng fallback response
        Log::error('Gemini API Error:', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);
        return $this->getFallbackResponse($message);
    }

    /**
     * Tạo system context để AI hiểu hệ thống
     */
    private function getSystemContext(string $userRole = 'guest', string $userName = 'Khách', $userId = null)
    {
        $roleSpecificInfo = $this->getRoleSpecificInfo($userRole);

        return "Bạn là trợ lý ảo của hệ thống Affiliate Marketing Platform.

👤 User: {$userName} ({$userRole})

{$roleSpecificInfo}

**QUAN TRỌNG:**
- Trả lời ngắn gọn, thân thiện, tự nhiên
- Tối đa 2-3 câu cho mỗi câu trả lời
- Sử dụng emoji phù hợp
- Hướng dẫn cụ thể theo vai trò của user
- Tránh câu trả lời dài dòng, phức tạp";
    }

    private function getSystemStats()
    {
        try {
            $systemInfoService = new SystemInfoService();
            return $systemInfoService->getSystemStats();
        } catch (\Exception $e) {
            return [
                'total_users' => 0,
                'total_products' => 0,
                'total_affiliate_links' => 0,
                'active_campaigns' => 0,
                'recent_conversions' => 0,
                'system_status' => 'maintenance'
            ];
        }
    }

    private function getUserContext($userId)
    {
        try {
            $systemInfoService = new SystemInfoService();
            return $systemInfoService->getUserContext($userId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Thông tin cụ thể theo vai trò
     */
    private function getRoleSpecificInfo(string $userRole)
    {
        switch ($userRole) {
            case 'admin':
                return "🔧 Admin: Quản lý hệ thống, users, báo cáo";

            case 'publisher':
                return "💰 Publisher: Tạo links, kiếm hoa hồng, theo dõi thu nhập";

            case 'shop':
                return "🛍️ Shop: Đăng sản phẩm, tạo campaigns, quản lý bán hàng";

            default:
                return "👋 Guest: Tìm hiểu hệ thống, đăng ký tài khoản";
        }
    }

    private function getFallbackResponse(string $message)
    {
        $message = strtolower($message);

        // Responses ngắn gọn và thân thiện
        if (strpos($message, 'xin chào') !== false || strpos($message, 'hello') !== false || strpos($message, 'chào') !== false) {
            return "Tôi có thể giúp gì cho bạn? 👋";
        }

        if (strpos($message, 'bạn là ai') !== false || strpos($message, 'who are you') !== false) {
            return "Tôi là trợ lý ảo của hệ thống affiliate marketing. 🤖";
        }

        if (strpos($message, 'hát') !== false || strpos($message, 'sing') !== false) {
            return "Tôi không thể hát được đâu! 😅 Nhưng tôi có thể giúp bạn với affiliate marketing.";
        }

        if (strpos($message, 'giúp') !== false || strpos($message, 'help') !== false) {
            return "Tôi có thể giúp bạn về affiliate marketing, tạo links, hoặc hướng dẫn sử dụng. Bạn cần gì? 🤔";
        }

        if (strpos($message, 'link') !== false) {
            return "Bạn có thể tạo affiliate links trong phần 'Affiliate Links' để kiếm hoa hồng. 🔗";
        }

        if (strpos($message, 'affiliate') !== false) {
            return "Affiliate marketing giúp bạn kiếm hoa hồng từ việc giới thiệu sản phẩm. 💰";
        }

        if (strpos($message, 'thu nhập') !== false || strpos($message, 'earning') !== false) {
            return "Xem thu nhập trong phần 'Wallet' hoặc 'Reports'. 💰";
        }

        // Default response ngắn gọn
        return "Tôi có thể giúp bạn với affiliate marketing. Bạn cần gì? 😊";
    }

}

