<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\SystemInfoService;

class GeminiService
{
    protected string $baseUrl = "https://generativelanguage.googleapis.com/v1beta/models";
    protected string $model = "gemini-2.5-flash";
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
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
        $systemStats = $this->getSystemStats();
        $userContext = $userId ? $this->getUserContext($userId) : null;

        $userInfo = "👤 **THÔNG TIN USER:**
- Tên: {$userName}
- Vai trò: {$userRole}
- Thời gian: " . now()->format('d/m/Y H:i');

        if ($userContext) {
            $userInfo .= "
- User ID: {$userContext['user_id']}
- Tham gia: {$userContext['created_at']}
- Lần đăng nhập cuối: {$userContext['last_login']}
- Số affiliate links: {$userContext['affiliate_links_count']}
- Tổng thu nhập: {$userContext['total_earnings']} VNĐ";
        }

        return "Bạn là trợ lý ảo của hệ thống Affiliate Marketing Platform. 

{$userInfo}

📊 **THỐNG KÊ HỆ THỐNG:**
- Tổng users: {$systemStats['total_users']}
- Tổng sản phẩm: {$systemStats['total_products']}
- Tổng affiliate links: {$systemStats['total_affiliate_links']}
- Campaigns đang hoạt động: {$systemStats['active_campaigns']}
- Conversions gần đây: {$systemStats['recent_conversions']}
- Trạng thái hệ thống: {$systemStats['system_status']}

🏢 **VỀ HỆ THỐNG:**
- Nền tảng affiliate marketing toàn diện
- Hỗ trợ 3 loại người dùng: Admin, Publisher, Shop
- Quản lý affiliate links, tracking, và hoa hồng

👥 **CÁC VAI TRÒ:**
- **Admin**: Quản lý toàn bộ hệ thống, xem báo cáo, quản lý users
- **Publisher**: Tạo affiliate links, kiếm hoa hồng từ việc giới thiệu sản phẩm
- **Shop**: Đăng sản phẩm, tạo affiliate campaigns, quản lý đơn hàng

🔗 **TÍNH NĂNG CHÍNH:**
- **Affiliate Links**: Tạo và quản lý links affiliate
- **Tracking**: Theo dõi clicks, conversions, hoa hồng
- **Wallet**: Quản lý thu nhập và thanh toán
- **Reports**: Báo cáo chi tiết về performance
- **Products**: Quản lý sản phẩm và campaigns

💰 **VỀ HOA HỒNG:**
- Hoa hồng được tính theo % hoặc số tiền cố định
- Thanh toán tự động khi đạt ngưỡng
- Theo dõi real-time thu nhập

📊 **BÁO CÁO:**
- Dashboard với thống kê tổng quan
- Báo cáo chi tiết theo thời gian
- Export dữ liệu

{$roleSpecificInfo}

Hãy trả lời các câu hỏi của user dựa trên thông tin này. Luôn hướng dẫn cụ thể và thực tế theo vai trò của họ.";
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
                return "🎯 **QUYỀN HẠN ADMIN:**
- Xem tất cả báo cáo và thống kê
- Quản lý users và permissions
- Cấu hình hệ thống
- Xem dashboard tổng quan
- Quản lý affiliate campaigns
- Xử lý thanh toán hoa hồng";

            case 'publisher':
                return "🎯 **QUYỀN HẠN PUBLISHER:**
- Tạo và quản lý affiliate links
- Xem báo cáo thu nhập cá nhân
- Theo dõi clicks và conversions
- Rút tiền từ wallet
- Xem danh sách sản phẩm có sẵn
- Tối ưu hóa affiliate links";

            case 'shop':
                return "🎯 **QUYỀN HẠN SHOP:**
- Đăng sản phẩm và tạo campaigns
- Quản lý affiliate links của sản phẩm
- Xem báo cáo bán hàng
- Quản lý đơn hàng và conversions
- Cấu hình hoa hồng cho publishers
- Theo dõi performance campaigns";

            default:
                return "🎯 **QUYỀN HẠN GUEST:**
- Xem thông tin cơ bản về hệ thống
- Tìm hiểu về affiliate marketing
- Đăng ký tài khoản mới
- Liên hệ hỗ trợ";
        }
    }

    private function getFallbackResponse(string $message)
    {
        $message = strtolower($message);

        // Responses thông minh hơn cho fallback
        if (strpos($message, 'xin chào') !== false || strpos($message, 'hello') !== false) {
            return "Xin chào! Tôi là trợ lý ảo của hệ thống affiliate marketing. Tôi có thể giúp bạn tìm hiểu về các tính năng của hệ thống, hướng dẫn sử dụng, hoặc hỗ trợ kỹ thuật.";
        }

        if (strpos($message, 'bạn là ai') !== false || strpos($message, 'who are you') !== false) {
            return "Tôi là trợ lý ảo của hệ thống affiliate marketing. Tôi có thể giúp bạn với các câu hỏi về hệ thống, hướng dẫn sử dụng, hoặc hỗ trợ kỹ thuật.";
        }

        if (strpos($message, 'giúp') !== false || strpos($message, 'help') !== false) {
            return "Tôi có thể giúp bạn với:\n• Hướng dẫn sử dụng hệ thống\n• Thông tin về affiliate marketing\n• Hỗ trợ kỹ thuật\n• Các câu hỏi thường gặp\n\nBạn muốn biết thêm về điều gì?";
        }

        if (strpos($message, 'affiliate') !== false) {
            return "Affiliate marketing là hình thức marketing trong đó bạn kiếm hoa hồng bằng cách quảng bá sản phẩm của người khác. Trong hệ thống này, bạn có thể tạo affiliate links và kiếm thu nhập từ việc giới thiệu sản phẩm.";
        }

        if (strpos($message, 'link') !== false) {
            return "Affiliate links là các liên kết đặc biệt giúp theo dõi việc chuyển đổi. Bạn có thể tạo link affiliate trong phần 'Affiliate Links' và chia sẻ chúng để kiếm hoa hồng.";
        }

        if (strpos($message, 'thu nhập') !== false || strpos($message, 'earning') !== false) {
            return "Thu nhập của bạn được tính dựa trên hoa hồng từ các chuyển đổi thành công. Bạn có thể xem chi tiết trong phần 'Wallet' hoặc 'Reports'.";
        }

        // Default response
        return "Cảm ơn bạn đã liên hệ! Tôi có thể giúp bạn với các câu hỏi về hệ thống affiliate marketing. Bạn có thể hỏi về cách sử dụng, tạo link affiliate, hoặc bất kỳ thông tin nào khác.";
    }

}

