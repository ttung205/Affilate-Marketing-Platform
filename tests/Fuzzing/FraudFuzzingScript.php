<?php

namespace Tests\Fuzzing;

use Tests\TestCase;
use App\Services\FraudDetectionService;
use Illuminate\Support\Str;

class FraudFuzzingScript extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected FraudDetectionService $fraudDetectionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fraudDetectionService = app(FraudDetectionService::class);
    }

    /**
     * Fuzz Testing: Đẩy 1000 input ngẫu nhiên để kiểm tra Crash và Memory Leak
     */
    public function test_fraud_fuzzing_robustness(): void
    {
        $iterations = 1000;
        $logFile = storage_path('logs/fuzzing_results.csv');
        
        $file = fopen($logFile, 'w');
        fputcsv($file, ['IP', 'User-Agent', 'Is_Fraud', 'Time_MS']);

        for ($i = 0; $i < $iterations; $i++) {
            $ip = $this->generateRandomIp();
            $userAgent = $this->generateRandomUserAgent();
            
            $startTime = microtime(true);
            
            try {
                // Đẩy rác vào Service
                /** @var \App\Models\AffiliateLink $link */
                $link = \Mockery::mock(\App\Models\AffiliateLink::class)->makePartial();
                $link->id = 1;
                $link->publisher_id = 99;

                $result = $this->fraudDetectionService->detectFraud($link, $ip, $userAgent);
                $isFraud = $result['is_fraud'];

                $timeMs = (microtime(true) - $startTime) * 1000;
                fputcsv($file, [$ip, $userAgent, $isFraud ? 'Yes' : 'No', round($timeMs, 2)]);
                
            } catch (\Throwable $e) {
                fclose($file);
                $this->fail("🔥 Fuzzing bị crash tại IP: {$ip}, UA: {$userAgent}. Lỗi: " . $e->getMessage());
            }
        }
        
        fclose($file);

        // Assertion chặn Memory Leak
        $peakMemoryUsage = memory_get_peak_usage(true) / 1024 / 1024; // Tính bằng MB
        $this->assertLessThan(
            64, 
            $peakMemoryUsage, 
            "⚠️ Phát hiện Memory leak! Hệ thống ngốn quá 64MB RAM sau khi xử lý: {$peakMemoryUsage}MB"
        );
    }

    /**
     * Helper: Sinh IP Fuzzing đa dạng định dạng
     */
    private function generateRandomIp(): string
    {
        $types = ['ipv4', 'ipv6', 'private_ipv4'];
        $type = $types[array_rand($types)];

        return match($type) {
            'ipv6' => implode(':', array_map(fn() => dechex(rand(0, 65535)), range(1, 8))),
            'private_ipv4' => '192.168.' . rand(0, 255) . '.' . rand(0, 255),
            default => rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255),
        };
    }

    /**
     * Helper: Sinh chuỗi User-Agent Fuzzing
     */
    private function generateRandomUserAgent(): string
    {
        $types = ['valid', 'bot', 'garbage'];
        $type = $types[array_rand($types)];

        return match($type) {
            'valid' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/' . rand(50, 120) . '.0.0.0',
            'bot' => 'Googlebot/' . rand(1, 3) . '.' . rand(0, 9),
            default => Str::random(rand(20, 200)), // Sinh chuỗi rác rất dài hoặc ký tự đặc biệt
        };
    }
}
