<?php

namespace Tests\Unit\P1_Fraud;

use Tests\TestCase;
use App\Services\FraudDetectionService;
use App\Models\AffiliateLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ReflectionMethod;

class FraudDetectionServiceTest extends TestCase
{
    use RefreshDatabase;
    protected FraudDetectionService $fraudDetectionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fraudDetectionService = app(FraudDetectionService::class);
    }

    /**
     * [TC-01] Kiểm tra logic phát hiện bot qua User-Agent (Valid Case)
     * @dataProvider validUserAgentProvider
     */
    public function test_bot_detection_logic_with_valid_ua(string $userAgent): void
    {
        $method = new ReflectionMethod(FraudDetectionService::class, 'isBotUserAgent');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->fraudDetectionService, $userAgent);
        $this->assertFalse($result['is_bot'], "Trình duyệt hợp lệ nhưng bị nhận diện là bot: {$userAgent}");
    }

    /**
     * [TC-01] Kiểm tra logic phát hiện bot qua User-Agent (Bot Case)
     * @dataProvider botUserAgentProvider
     */
    public function test_bot_detection_logic_with_bot_ua(string $userAgent): void
    {
        $method = new ReflectionMethod(FraudDetectionService::class, 'isBotUserAgent');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->fraudDetectionService, $userAgent);
        $this->assertTrue($result['is_bot'], "Bot nguy hiểm nhưng hệ thống không phát hiện: {$userAgent}");
    }

    /**
     * [TC-02] Kiểm tra logic Rate Limit IP dựa vào Cache
     */
    public function test_ip_rate_limit_calculation(): void
    {
        $ip = '192.168.1.100';
        
        /** @var \App\Models\AffiliateLink $link */
        $link = \Mockery::mock(AffiliateLink::class)->makePartial();
        $link->id = 1;
        $link->publisher_id = 99;
        
        // Mock Cache return value for 11 clicks in an hour
        Cache::shouldReceive('remember')->with("clicks_per_ip_hour:{$ip}", \Mockery::any(), \Mockery::any())->andReturn(11); // Over 10
        Cache::shouldReceive('remember')->with("clicks_per_ip_day:{$ip}", \Mockery::any(), \Mockery::any())->andReturn(11);
        Cache::shouldReceive('remember')->with("clicks_per_link_ip_day:1:{$ip}", \Mockery::any(), \Mockery::any())->andReturn(2);
        Cache::shouldReceive('remember')->with("publisher_ips:99", \Mockery::any(), \Mockery::any())->andReturn([]);

        // Mock DB for logging
        DB::shouldReceive('table')->with('click_fraud_logs')->andReturnSelf();
        DB::shouldReceive('insert')->andReturn(true);
        Log::shouldReceive('warning')->andReturnNull();

        $result = $this->fraudDetectionService->detectFraud($link, $ip, 'Mozilla/5.0 Chrome/115.0.0.0');
        
        $this->assertTrue($result['is_fraud'], 'Phải chặn vì vượt ngưỡng Rate Limit IP/Hour');
        $this->assertStringContainsString('Quá nhiều clicks từ IP trong 1 giờ', $result['reason']);
    }

    /**
     * [TC-03] Kiểm tra cộng dồn điểm rủi ro (Risk Score)
     */
    public function test_risk_score_aggregation(): void
    {
        $ip = '10.0.0.1';
        /** @var \App\Models\AffiliateLink $link */
        $link = \Mockery::mock(AffiliateLink::class)->makePartial();
        $link->id = 1;
        $link->publisher_id = 99;

        // Bot: +100 điểm, IP Hourly RateLimit: +50, IP Daily RateLimit: +70
        Cache::shouldReceive('remember')->with("clicks_per_ip_hour:{$ip}", \Mockery::any(), \Mockery::any())->andReturn(15);
        Cache::shouldReceive('remember')->with("clicks_per_ip_day:{$ip}", \Mockery::any(), \Mockery::any())->andReturn(60);
        Cache::shouldReceive('remember')->with("clicks_per_link_ip_day:1:{$ip}", \Mockery::any(), \Mockery::any())->andReturn(1);
        Cache::shouldReceive('remember')->with("publisher_ips:99", \Mockery::any(), \Mockery::any())->andReturn([]);

        DB::shouldReceive('table')->with('click_fraud_logs')->andReturnSelf();
        DB::shouldReceive('insert')->andReturn(true);
        Log::shouldReceive('warning')->andReturnNull();

        $result = $this->fraudDetectionService->detectFraud($link, $ip, 'curl/7.81.0');
        
        $this->assertGreaterThanOrEqual(100, $result['risk_score']);
        $this->assertTrue($result['is_fraud']);
    }

    /**
     * [TC-04] Kiểm tra phòng chống Publisher tự click link của chính mình
     */
    public function test_self_click_prevention(): void
    {
        $method = new ReflectionMethod(FraudDetectionService::class, 'isPublisherSelfClicking');
        $method->setAccessible(true);
        
        /** @var \App\Models\AffiliateLink $link */
        $link = \Mockery::mock(AffiliateLink::class)->makePartial();
        $link->id = 1;
        $link->publisher_id = 99;
        
        // Mock Cache chứa IP của publisher
        Cache::shouldReceive('remember')->with("publisher_ips:99", \Mockery::any(), \Mockery::any())->andReturn(['192.168.1.1']);
        
        $isSelf = $method->invoke($this->fraudDetectionService, $link, '192.168.1.1');
        $this->assertTrue($isSelf, 'Phải phát hiện tự click khi trùng IP');

        $isNotSelf = $method->invoke($this->fraudDetectionService, $link, '10.0.0.1');
        $this->assertFalse($isNotSelf, 'Cho qua khi khác IP');
    }

    public static function validUserAgentProvider(): array
    {
        return [
            ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/115.0.0.0 Safari/537.36'],
            ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/115.0'],
            ['Mozilla/5.0 (iPhone; CPU iPhone OS 16_5 like Mac OS X) AppleWebKit/605.1.15 Version/16.5 Mobile/15E148 Safari/604.1'],
        ];
    }

    public static function botUserAgentProvider(): array
    {
        return [
            ['curl/7.81.0'],
            ['python-requests/2.28.1'],
            ['Scrapy/2.5.1 (+https://scrapy.org)'],
            ['Wget/1.21.2'],
        ];
    }
}
