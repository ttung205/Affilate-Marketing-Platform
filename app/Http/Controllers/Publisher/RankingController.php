<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Services\PublisherRankingService;
use App\Models\PublisherRanking;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    protected PublisherRankingService $rankingService;

    public function __construct(PublisherRankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    /**
     * Hiển thị trang hạng của publisher
     */
    public function index()
    {
        $publisher = auth()->user();
        $currentRanking = $publisher->publisherRanking;
        $progress = $this->rankingService->getRankingProgress($publisher);

        // Lấy tất cả hạng để hiển thị
        $allRankings = PublisherRanking::active()->ordered()->get();

        return view('publisher.ranking.index', compact(
            'publisher',
            'currentRanking',
            'progress',
            'allRankings'
        ));
    }

    /**
     * API: Lấy thông tin hạng hiện tại
     */
    public function getCurrentRanking()
    {
        $publisher = auth()->user();
        $currentRanking = $publisher->publisherRanking;
        $progress = $this->rankingService->getRankingProgress($publisher);

        return response()->json([
            'success' => true,
            'data' => [
                'current_ranking' => $currentRanking,
                'progress' => $progress,
                'stats' => [
                    'total_links' => $publisher->affiliateLinks()->count(),
                    'total_commission' => $publisher->getCombinedCommissionAttribute(),
                ]
            ]
        ]);
    }

    /**
     * API: Cập nhật hạng cho publisher hiện tại
     */
    public function updateRanking()
    {
        $publisher = auth()->user();
        $oldRanking = $publisher->publisherRanking;

        $newRanking = $this->rankingService->updatePublisherRanking($publisher);

        if ($newRanking) {
            $isUpgrade = !$oldRanking || $newRanking->level > $oldRanking->level;

            return response()->json([
                'success' => true,
                'message' => $isUpgrade ? 'Chúc mừng! Bạn đã thăng hạng!' : 'Hạng đã được cập nhật',
                'data' => [
                    'old_ranking' => $oldRanking,
                    'new_ranking' => $newRanking,
                    'is_upgrade' => $isUpgrade
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Chưa đạt tiêu chí để có hạng'
        ]);
    }

    /**
     * Hiển thị bảng xếp hạng publisher
     */
    public function leaderboard()
    {
        $rankings = PublisherRanking::active()->ordered()->get();
        $leaderboard = [];

        foreach ($rankings as $ranking) {
            $publishers = $this->rankingService->getPublishersByRanking($ranking)
                ->sortByDesc(function ($publisher) {
                    return $publisher->getCombinedCommissionAttribute();
                })
                ->take(10); // Top 10 mỗi hạng

            $leaderboard[] = [
                'ranking' => $ranking,
                'publishers' => $publishers
            ];
        }

        return view('publisher.ranking.leaderboard', compact('leaderboard'));
    }

    /**
     * API: Lấy bảng xếp hạng
     */
    public function getLeaderboard()
    {
        $rankings = PublisherRanking::active()->ordered()->get();
        $leaderboard = [];

        foreach ($rankings as $ranking) {
            $publishers = $this->rankingService->getPublishersByRanking($ranking)
                ->sortByDesc(function ($publisher) {
                    return $publisher->getCombinedCommissionAttribute();
                })
                ->take(5) // Top 5 mỗi hạng
                ->map(function ($publisher) {
                    return [
                        'id' => $publisher->id,
                        'name' => $publisher->name,
                        'avatar' => $publisher->avatar,
                        'total_commission' => $publisher->getCombinedCommissionAttribute(),
                        'total_links' => $publisher->affiliateLinks()->count(),
                    ];
                });

            $leaderboard[] = [
                'ranking' => [
                    'name' => $ranking->name,
                    'icon' => $ranking->icon,
                    'color' => $ranking->color,
                ],
                'publishers' => $publishers
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $leaderboard
        ]);
    }
}
