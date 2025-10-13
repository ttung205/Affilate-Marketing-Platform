<?php

namespace App\Services;

use App\Models\AffiliateLink;
use App\Models\Click;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FraudDetectionService
{
    // Fraud detection thresholds
    private const MAX_CLICKS_PER_IP_PER_HOUR = 10;
    private const MAX_CLICKS_PER_IP_PER_DAY = 50;
    private const MAX_CLICKS_PER_LINK_PER_IP_PER_DAY = 3;
    private const SUSPICIOUS_USER_AGENT_THRESHOLD = 0.7;
    
    // Known bot patterns
    private const BOT_PATTERNS = [
        'bot', 'crawl', 'spider', 'scraper', 'curl', 'wget', 'python',
        'java', 'perl', 'ruby', 'php', 'scrape', 'harvest', 'extract',
        'archiver', 'validator', 'monitor', 'checker', 'scan', 'headless'
    ];

    // Known legitimate bots (allow these)
    private const LEGITIMATE_BOTS = [
        'googlebot', 'bingbot', 'slackbot', 'twitterbot', 'facebookexternalhit',
        'linkedinbot', 'whatsapp', 'telegrambot'
    ];

    /**
     * Kiểm tra click có phải fraud không
     * 
     * @param AffiliateLink $affiliateLink
     * @param string $ipAddress
     * @param string $userAgent
     * @return array ['is_fraud' => bool, 'reason' => string, 'risk_score' => int]
     */
    public function detectFraud(AffiliateLink $affiliateLink, string $ipAddress, string $userAgent): array
    {
        $checks = [];
        $riskScore = 0;

        // 1. Bot Detection
        $botCheck = $this->isBotUserAgent($userAgent);
        if ($botCheck['is_bot']) {
            $checks[] = 'Bot detected: ' . $botCheck['reason'];
            $riskScore += 100; // Instant fraud
        }

        // 2. Rate Limiting - Clicks per IP per Hour
        $clicksPerHour = $this->getClicksPerIpPerHour($ipAddress);
        if ($clicksPerHour >= self::MAX_CLICKS_PER_IP_PER_HOUR) {
            $checks[] = "Too many clicks per hour from IP: {$clicksPerHour}";
            $riskScore += 50;
        }

        // 3. Rate Limiting - Clicks per IP per Day
        $clicksPerDay = $this->getClicksPerIpPerDay($ipAddress);
        if ($clicksPerDay >= self::MAX_CLICKS_PER_IP_PER_DAY) {
            $checks[] = "Too many clicks per day from IP: {$clicksPerDay}";
            $riskScore += 70;
        }

        // 4. Duplicate Click - Same IP clicking same link multiple times
        $clicksPerLinkPerDay = $this->getClicksPerLinkPerIpPerDay($affiliateLink->id, $ipAddress);
        if ($clicksPerLinkPerDay >= self::MAX_CLICKS_PER_LINK_PER_IP_PER_DAY) {
            $checks[] = "Duplicate clicks on same link: {$clicksPerLinkPerDay}";
            $riskScore += 30;
        }

        // 5. Empty or Suspicious User Agent
        if (empty($userAgent) || strlen($userAgent) < 10) {
            $checks[] = 'Empty or too short user agent';
            $riskScore += 40;
        }

        // 6. Publisher Self-Clicking (IP match)
        if ($this->isPublisherSelfClicking($affiliateLink, $ipAddress)) {
            $checks[] = 'Publisher clicking own link (IP match)';
            $riskScore += 80;
        }

        // 7. Rapid Sequential Clicks (< 2 seconds apart)
        if ($this->hasRapidSequentialClicks($ipAddress)) {
            $checks[] = 'Rapid sequential clicks detected';
            $riskScore += 60;
        }

        // Determine if fraud (risk score >= 50 is considered fraud)
        $isFraud = $riskScore >= 50;

        // Log fraud attempt
        if ($isFraud) {
            $this->logFraudAttempt($affiliateLink, $ipAddress, $userAgent, $checks, $riskScore);
        }

        return [
            'is_fraud' => $isFraud,
            'reason' => implode('; ', $checks),
            'risk_score' => $riskScore,
            'checks' => $checks,
        ];
    }

    /**
     * Kiểm tra user agent có phải bot không
     */
    private function isBotUserAgent(string $userAgent): array
    {
        $userAgentLower = strtolower($userAgent);

        // Check legitimate bots first (allow them for SEO)
        foreach (self::LEGITIMATE_BOTS as $legitBot) {
            if (strpos($userAgentLower, $legitBot) !== false) {
                return [
                    'is_bot' => false, // Don't flag as fraud
                    'reason' => "Legitimate bot: {$legitBot}"
                ];
            }
        }

        // Check malicious/scraper bots
        foreach (self::BOT_PATTERNS as $pattern) {
            if (strpos($userAgentLower, $pattern) !== false) {
                return [
                    'is_bot' => true,
                    'reason' => "Bot pattern matched: {$pattern}"
                ];
            }
        }

        // Check for suspicious characteristics
        if ($this->hasSuspiciousUserAgentCharacteristics($userAgent)) {
            return [
                'is_bot' => true,
                'reason' => 'Suspicious user agent characteristics'
            ];
        }

        return ['is_bot' => false, 'reason' => ''];
    }

    /**
     * Kiểm tra user agent có đặc điểm đáng ngờ
     */
    private function hasSuspiciousUserAgentCharacteristics(string $userAgent): bool
    {
        // Too short
        if (strlen($userAgent) < 20) {
            return true;
        }

        // Doesn't contain common browser indicators
        $browserIndicators = ['mozilla', 'chrome', 'safari', 'firefox', 'edge', 'opera'];
        $hasValidBrowser = false;
        foreach ($browserIndicators as $indicator) {
            if (stripos($userAgent, $indicator) !== false) {
                $hasValidBrowser = true;
                break;
            }
        }

        if (!$hasValidBrowser) {
            return true;
        }

        return false;
    }

    /**
     * Đếm số clicks từ IP trong 1 giờ qua
     */
    private function getClicksPerIpPerHour(string $ipAddress): int
    {
        $cacheKey = "clicks_per_ip_hour:{$ipAddress}";
        
        return Cache::remember($cacheKey, 3600, function () use ($ipAddress) {
            return Click::where('ip_address', $ipAddress)
                ->where('clicked_at', '>=', now()->subHour())
                ->count();
        });
    }

    /**
     * Đếm số clicks từ IP trong 24 giờ qua
     */
    private function getClicksPerIpPerDay(string $ipAddress): int
    {
        $cacheKey = "clicks_per_ip_day:{$ipAddress}";
        
        return Cache::remember($cacheKey, 3600, function () use ($ipAddress) {
            return Click::where('ip_address', $ipAddress)
                ->where('clicked_at', '>=', now()->subDay())
                ->count();
        });
    }

    /**
     * Đếm số clicks từ IP cho một link cụ thể trong ngày
     */
    private function getClicksPerLinkPerIpPerDay(int $affiliateLinkId, string $ipAddress): int
    {
        $cacheKey = "clicks_per_link_ip_day:{$affiliateLinkId}:{$ipAddress}";
        
        return Cache::remember($cacheKey, 3600, function () use ($affiliateLinkId, $ipAddress) {
            return Click::where('affiliate_link_id', $affiliateLinkId)
                ->where('ip_address', $ipAddress)
                ->where('clicked_at', '>=', now()->subDay())
                ->count();
        });
    }

    /**
     * Kiểm tra publisher có đang tự click link của mình không
     */
    private function isPublisherSelfClicking(AffiliateLink $affiliateLink, string $ipAddress): bool
    {
        // Get publisher's recent login IPs from cache or database
        $publisherId = $affiliateLink->publisher_id;
        $cacheKey = "publisher_ips:{$publisherId}";
        
        $publisherIps = Cache::remember($cacheKey, 3600, function () use ($publisherId) {
            // Get IPs from recent clicks by this publisher on their own links
            return Click::where('publisher_id', $publisherId)
                ->where('clicked_at', '>=', now()->subDays(7))
                ->distinct()
                ->pluck('ip_address')
                ->toArray();
        });

        return in_array($ipAddress, $publisherIps);
    }

    /**
     * Kiểm tra có clicks nhanh liên tiếp không (< 2 giây)
     */
    private function hasRapidSequentialClicks(string $ipAddress): bool
    {
        $lastClick = Click::where('ip_address', $ipAddress)
            ->orderBy('clicked_at', 'desc')
            ->first();

        if (!$lastClick) {
            return false;
        }

        // If last click was less than 2 seconds ago
        return $lastClick->clicked_at->diffInSeconds(now()) < 2;
    }

    /**
     * Ghi log fraud attempt vào database
     */
    private function logFraudAttempt(
        AffiliateLink $affiliateLink, 
        string $ipAddress, 
        string $userAgent, 
        array $reasons, 
        int $riskScore
    ): void {
        try {
            DB::table('click_fraud_logs')->insert([
                'affiliate_link_id' => $affiliateLink->id,
                'publisher_id' => $affiliateLink->publisher_id,
                'product_id' => $affiliateLink->product_id,
                'campaign_id' => $affiliateLink->campaign_id,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'reasons' => json_encode($reasons),
                'risk_score' => $riskScore,
                'detected_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::warning('Fraud attempt detected', [
                'affiliate_link_id' => $affiliateLink->id,
                'publisher_id' => $affiliateLink->publisher_id,
                'ip_address' => $ipAddress,
                'risk_score' => $riskScore,
                'reasons' => $reasons,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log fraud attempt', [
                'error' => $e->getMessage(),
                'ip_address' => $ipAddress,
            ]);
        }
    }

    /**
     * Increment click counter trong cache (for rate limiting)
     */
    public function incrementClickCounter(string $ipAddress): void
    {
        // Increment hourly counter
        $hourKey = "clicks_per_ip_hour:{$ipAddress}";
        Cache::increment($hourKey);
        if (!Cache::has($hourKey)) {
            Cache::put($hourKey, 1, 3600); // 1 hour
        }

        // Increment daily counter
        $dayKey = "clicks_per_ip_day:{$ipAddress}";
        Cache::increment($dayKey);
        if (!Cache::has($dayKey)) {
            Cache::put($dayKey, 1, 86400); // 24 hours
        }
    }

    /**
     * Block IP address (thêm vào blacklist)
     */
    public function blockIpAddress(string $ipAddress, string $reason): void
    {
        Cache::put("blocked_ip:{$ipAddress}", [
            'reason' => $reason,
            'blocked_at' => now(),
        ], 86400 * 30); // Block for 30 days

        Log::warning("IP address blocked", [
            'ip_address' => $ipAddress,
            'reason' => $reason,
        ]);
    }

    /**
     * Kiểm tra IP có bị block không
     */
    public function isIpBlocked(string $ipAddress): bool
    {
        return Cache::has("blocked_ip:{$ipAddress}");
    }

    /**
     * Get fraud statistics for admin dashboard
     */
    public function getFraudStatistics(int $days = 7): array
    {
        $startDate = now()->subDays($days);

        return [
            'total_fraud_attempts' => DB::table('click_fraud_logs')
                ->where('detected_at', '>=', $startDate)
                ->count(),
            
            'fraud_by_reason' => DB::table('click_fraud_logs')
                ->where('detected_at', '>=', $startDate)
                ->select(DB::raw('reasons, COUNT(*) as count'))
                ->groupBy('reasons')
                ->get(),
            
            'top_fraud_ips' => DB::table('click_fraud_logs')
                ->where('detected_at', '>=', $startDate)
                ->select('ip_address', DB::raw('COUNT(*) as attempts'))
                ->groupBy('ip_address')
                ->orderBy('attempts', 'desc')
                ->limit(10)
                ->get(),
            
            'fraud_by_publisher' => DB::table('click_fraud_logs')
                ->where('detected_at', '>=', $startDate)
                ->select('publisher_id', DB::raw('COUNT(*) as attempts'))
                ->groupBy('publisher_id')
                ->orderBy('attempts', 'desc')
                ->limit(10)
                ->get(),
            
            'average_risk_score' => DB::table('click_fraud_logs')
                ->where('detected_at', '>=', $startDate)
                ->avg('risk_score'),
        ];
    }

    /**
     * Clear cache cho fraud detection
     */
    public function clearCache(string $ipAddress = null): void
    {
        if ($ipAddress) {
            Cache::forget("clicks_per_ip_hour:{$ipAddress}");
            Cache::forget("clicks_per_ip_day:{$ipAddress}");
            Cache::forget("blocked_ip:{$ipAddress}");
        } else {
            // Clear all fraud detection cache
            Cache::flush();
        }
    }
}

