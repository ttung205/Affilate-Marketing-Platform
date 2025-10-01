<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Rate limit for withdrawal creation - 3 successful withdrawals per 10 minutes
        RateLimiter::for('withdrawal', function (Request $request) {
            return Limit::perMinutes(10, 3)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn đã vượt quá giới hạn yêu cầu rút tiền. Vui lòng thử lại sau 10 phút.',
                        'retry_after' => $headers['Retry-After'] ?? 600
                    ], 429);
                });
        });

        // Rate limit for OTP resend - 5 attempts per 10 minutes
        RateLimiter::for('withdrawal-otp', function (Request $request) {
            return Limit::perMinutes(10, 5)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn đã gửi lại OTP quá nhiều lần. Vui lòng thử lại sau 10 phút.',
                        'retry_after' => $headers['Retry-After'] ?? 600
                    ], 429);
                });
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
