<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WithdrawalRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = Auth::id();
        $ip = $request->ip();
        
        // Rate limits
        $userKey = "withdrawal_attempts_user_{$userId}";
        $ipKey = "withdrawal_attempts_ip_{$ip}";
        $failedKey = "withdrawal_failed_user_{$userId}";
        
        // Check failed attempts (max 3 failed attempts = 15 min lock)
        $failedAttempts = Cache::get($failedKey, 0);
        if ($failedAttempts >= 3) {
            Log::warning('Withdrawal blocked due to failed attempts', [
                'user_id' => $userId,
                'ip' => $ip,
                'failed_attempts' => $failedAttempts
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản tạm thời bị khóa do quá nhiều lần thất bại. Vui lòng thử lại sau 15 phút.',
                'retry_after' => 900
            ], 429);
        }
        
        // Check user rate limit (5 requests per hour)
        $userAttempts = Cache::get($userKey, 0);
        if ($userAttempts >= 5) {
            Log::warning('Withdrawal rate limit exceeded for user', [
                'user_id' => $userId,
                'attempts' => $userAttempts
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã vượt quá giới hạn 5 yêu cầu rút tiền mỗi giờ. Vui lòng thử lại sau.',
                'retry_after' => 3600
            ], 429);
        }
        
        // Check IP rate limit (10 requests per hour)
        $ipAttempts = Cache::get($ipKey, 0);
        if ($ipAttempts >= 10) {
            Log::warning('Withdrawal rate limit exceeded for IP', [
                'ip' => $ip,
                'attempts' => $ipAttempts
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'IP này đã vượt quá giới hạn yêu cầu. Vui lòng thử lại sau.',
                'retry_after' => 3600
            ], 429);
        }
        
        // Increment counters
        Cache::put($userKey, $userAttempts + 1, now()->addHour());
        Cache::put($ipKey, $ipAttempts + 1, now()->addHour());
        
        $response = $next($request);
        
        // If withdrawal failed, increment failed counter
        if ($response->getStatusCode() >= 400) {
            Cache::put($failedKey, $failedAttempts + 1, now()->addMinutes(15));
        } else {
            // Reset failed counter on success
            Cache::forget($failedKey);
        }
        
        return $response;
    }
}
