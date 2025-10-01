<?php

namespace App\Services;

use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CacheService
{
    const CACHE_TTL = [
        'stats' => 300, // 5 minutes
        'user_data' => 3600, // 1 hour
        'withdrawal_list' => 60, // 1 minute
        'permissions' => 7200, // 2 hours
    ];

    /**
     * Get cached withdrawal stats
     */
    public function getWithdrawalStats(): array
    {
        return Cache::remember('withdrawal_stats', self::CACHE_TTL['stats'], function () {
            return [
                'pending_count' => Withdrawal::where('status', 'pending')->count(),
                'approved_count' => Withdrawal::where('status', 'approved')->count(),
                'completed_count' => Withdrawal::where('status', 'completed')->count(),
                'rejected_count' => Withdrawal::where('status', 'rejected')->count(),
                'total_amount' => Withdrawal::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('amount'),
                'pending_amount' => Withdrawal::where('status', 'pending')->sum('amount'),
                'processing_amount' => Withdrawal::whereIn('status', ['approved', 'processing'])->sum('amount'),
            ];
        });
    }

    /**
     * Get cached user withdrawal stats
     */
    public function getUserWithdrawalStats(User $user): array
    {
        $cacheKey = "user_withdrawal_stats_{$user->id}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL['user_data'], function () use ($user) {
            return [
                'total_withdrawals' => $user->withdrawals()->count(),
                'total_amount' => $user->withdrawals()->sum('amount'),
                'pending_amount' => $user->withdrawals()->where('status', 'pending')->sum('amount'),
                'completed_amount' => $user->withdrawals()->where('status', 'completed')->sum('amount'),
                'this_month' => [
                    'count' => $user->withdrawals()
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->count(),
                    'amount' => $user->withdrawals()
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->sum('amount'),
                ],
                'success_rate' => $this->calculateSuccessRate($user),
            ];
        });
    }

    /**
     * Get cached withdrawal list with filters
     */
    public function getWithdrawalList(array $filters = [], int $perPage = 15): string
    {
        $cacheKey = 'withdrawal_list_' . md5(serialize($filters) . $perPage);
        
        return Cache::remember($cacheKey, self::CACHE_TTL['withdrawal_list'], function () use ($filters, $perPage) {
            $query = Withdrawal::with(['publisher', 'paymentMethod', 'processedBy']);
            
            // Apply filters
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            
            if (!empty($filters['date_from'])) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            }
            
            if (!empty($filters['date_to'])) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            }
            
            if (!empty($filters['amount_min'])) {
                $query->where('amount', '>=', $filters['amount_min']);
            }
            
            if (!empty($filters['amount_max'])) {
                $query->where('amount', '<=', $filters['amount_max']);
            }
            
            if (!empty($filters['publisher'])) {
                $query->whereHas('publisher', function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['publisher'] . '%')
                      ->orWhere('email', 'like', '%' . $filters['publisher'] . '%');
                });
            }
            
            return $query->orderBy('created_at', 'desc')->paginate($perPage);
        });
    }

    /**
     * Get cached user permissions
     */
    public function getUserPermissions(User $user): array
    {
        $cacheKey = "user_permissions_{$user->id}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL['permissions'], function () use ($user) {
            return [
                'can_withdraw' => $user->role === 'publisher',
                'can_approve_withdrawals' => in_array($user->role, ['admin', 'super_admin']),
                'can_bulk_approve' => $user->role === 'super_admin',
                'withdrawal_limit' => $user->role === 'publisher' ? 5000000 : 0,
                'requires_2fa' => $user->two_factor_enabled ?? false,
            ];
        });
    }

    /**
     * Invalidate withdrawal related caches
     */
    public function invalidateWithdrawalCaches(Withdrawal $withdrawal = null): void
    {
        $patterns = [
            'withdrawal_stats',
            'withdrawal_list_*',
        ];
        
        if ($withdrawal) {
            $patterns[] = "user_withdrawal_stats_{$withdrawal->publisher_id}";
            $patterns[] = "user_permissions_{$withdrawal->publisher_id}";
        }
        
        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                $this->forgetByPattern($pattern);
            } else {
                Cache::forget($pattern);
            }
        }
        
        Log::info('Withdrawal caches invalidated', [
            'withdrawal_id' => $withdrawal?->id,
            'patterns' => $patterns
        ]);
    }

    /**
     * Invalidate user specific caches
     */
    public function invalidateUserCaches(User $user): void
    {
        $patterns = [
            "user_withdrawal_stats_{$user->id}",
            "user_permissions_{$user->id}",
        ];
        
        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
        
        Log::info('User caches invalidated', [
            'user_id' => $user->id,
            'patterns' => $patterns
        ]);
    }

    /**
     * Warm up frequently accessed caches
     */
    public function warmUpCaches(): void
    {
        Log::info('Starting cache warm up');
        
        // Warm up withdrawal stats
        $this->getWithdrawalStats();
        
        // Warm up common withdrawal lists
        $commonFilters = [
            ['status' => 'pending'],
            ['status' => 'approved'],
            ['status' => 'completed'],
            []
        ];
        
        foreach ($commonFilters as $filters) {
            $this->getWithdrawalList($filters);
        }
        
        Log::info('Cache warm up completed');
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        $keys = [
            'withdrawal_stats',
            'withdrawal_list_*',
            'user_withdrawal_stats_*',
            'user_permissions_*',
        ];
        
        $stats = [
            'total_keys' => 0,
            'hit_rate' => 0,
            'memory_usage' => 0,
        ];
        
        // This would need Redis or Memcached specific implementation
        // For now, return basic stats
        return $stats;
    }

    /**
     * Calculate user success rate
     */
    private function calculateSuccessRate(User $user): float
    {
        $totalWithdrawals = $user->withdrawals()->count();
        
        if ($totalWithdrawals === 0) {
            return 0.0;
        }
        
        $successfulWithdrawals = $user->withdrawals()
            ->where('status', 'completed')
            ->count();
            
        return round(($successfulWithdrawals / $totalWithdrawals) * 100, 2);
    }

    /**
     * Forget cache keys by pattern
     */
    private function forgetByPattern(string $pattern): void
    {
        // This is a simplified implementation
        // In production, you might want to use Redis SCAN or similar
        $keys = Cache::getRedis()->keys(str_replace('*', '*', $pattern));
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
