<?php

namespace App\Services;

use App\Models\User;
use App\Models\PublisherRanking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublisherRankingService
{
    /**
     * Cập nhật hạng cho một publisher
     */
    public function updatePublisherRanking(User $publisher): ?PublisherRanking
    {
        if (!$publisher->isPublisher()) {
            return null;
        }

        try {
            DB::beginTransaction();

            $totalLinks = $publisher->affiliateLinks()->count();
            $totalCommission = $publisher->getCombinedCommissionAttribute();

            // Tìm hạng phù hợp dựa trên tiêu chí
            $newRanking = $this->calculateRanking($totalLinks, $totalCommission);

            if (!$newRanking) {
                DB::rollBack();
                return null;
            }

            $currentRanking = $publisher->publisherRanking;
            $isUpgrade = !$currentRanking || $newRanking->level > $currentRanking->level;

            // Cập nhật hạng cho publisher
            $publisher->update([
                'publisher_ranking_id' => $newRanking->id,
                'ranking_achieved_at' => $isUpgrade ? now() : $publisher->ranking_achieved_at,
                'last_ranking_check_at' => now(),
            ]);

            // Log nếu có thăng hạng
            if ($isUpgrade) {
                Log::info("Publisher ranking upgraded", [
                    'publisher_id' => $publisher->id,
                    'old_ranking' => $currentRanking?->name ?? 'Chưa có hạng',
                    'new_ranking' => $newRanking->name,
                    'total_links' => $totalLinks,
                    'total_commission' => $totalCommission,
                ]);
            }

            DB::commit();
            return $newRanking;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating publisher ranking", [
                'publisher_id' => $publisher->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Tính hạng dựa trên số link và hoa hồng
     */
    public function calculateRanking(int $totalLinks, float $totalCommission): ?PublisherRanking
    {
        // Lấy tất cả hạng theo thứ tự từ cao xuống thấp
        $rankings = PublisherRanking::active()
            ->orderBy('level', 'desc')
            ->get();

        foreach ($rankings as $ranking) {
            if ($totalLinks >= $ranking->min_links && $totalCommission >= $ranking->min_commission) {
                return $ranking;
            }
        }

        return null; // Không đạt hạng nào
    }

    /**
     * Cập nhật hạng cho tất cả publisher
     */
    public function updateAllPublisherRankings(): array
    {
        $results = [
            'total_checked' => 0,
            'upgraded' => 0,
            'errors' => 0,
        ];

        $publishers = User::whereIn('role', ['publisher', 'shop'])
            ->where('is_active', true)
            ->get();

        foreach ($publishers as $publisher) {
            $results['total_checked']++;

            $oldRanking = $publisher->publisherRanking;
            $newRanking = $this->updatePublisherRanking($publisher);

            if ($newRanking && (!$oldRanking || $newRanking->level > $oldRanking->level)) {
                $results['upgraded']++;
            }
        }

        Log::info("Bulk ranking update completed", $results);
        return $results;
    }

    /**
     * Lấy tiến độ để đạt hạng tiếp theo
     */
    public function getRankingProgress(User $publisher): array
    {
        $currentRanking = $publisher->publisherRanking;
        $nextRanking = $currentRanking ? PublisherRanking::getNextRanking($currentRanking) : PublisherRanking::getByLevel(1);

        if (!$nextRanking) {
            return [
                'current_ranking' => $currentRanking,
                'next_ranking' => null,
                'is_max_level' => true,
                'progress' => [
                    'links_progress' => 100,
                    'commission_progress' => 100,
                ]
            ];
        }

        $totalLinks = $publisher->affiliateLinks()->count();
        $totalCommission = $publisher->getCombinedCommissionAttribute();

        $linksProgress = min(100, ($totalLinks / $nextRanking->min_links) * 100);
        $commissionProgress = min(100, ($totalCommission / $nextRanking->min_commission) * 100);

        return [
            'current_ranking' => $currentRanking,
            'next_ranking' => $nextRanking,
            'is_max_level' => false,
            'progress' => [
                'links_progress' => round($linksProgress, 1),
                'commission_progress' => round($commissionProgress, 1),
                'links_needed' => max(0, $nextRanking->min_links - $totalLinks),
                'commission_needed' => max(0, $nextRanking->min_commission - $totalCommission),
            ],
            'stats' => [
                'total_links' => $totalLinks,
                'total_commission' => $totalCommission,
            ]
        ];
    }

    /**
     * Tính bonus hoa hồng dựa trên hạng
     */
    public function calculateBonusCommission(User $publisher, float $baseCommission): float
    {
        $ranking = $publisher->publisherRanking;

        if (!$ranking || $ranking->bonus_percentage <= 0) {
            return 0;
        }

        return ($baseCommission * $ranking->bonus_percentage) / 100;
    }

    /**
     * Lấy danh sách publisher theo hạng
     */
    public function getPublishersByRanking(?PublisherRanking $ranking = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::whereIn('role', ['publisher', 'shop'])
            ->where('is_active', true)
            ->with(['publisherRanking', 'affiliateLinks']);

        if ($ranking) {
            $query->where('publisher_ranking_id', $ranking->id);
        }

        return $query->get();
    }

    /**
     * Lấy thống kê hạng publisher
     */
    public function getRankingStats(): array
    {
        $stats = [];

        $rankings = PublisherRanking::active()->ordered()->get();

        foreach ($rankings as $ranking) {
            $publisherCount = $this->getPublishersByRanking($ranking)->count();

            $stats[] = [
                'ranking' => $ranking,
                'publisher_count' => $publisherCount,
            ];
        }

        return $stats;
    }
}
