<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Services\PublisherService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    public function __construct(
        private PublisherService $publisherService
    ) {}

    /**
     * Hiển thị trang ví
     */
    public function index()
    {
        $publisher = Auth::user();
        
        Log::info('Wallet page accessed', [
            'user_id' => $publisher->id
        ]);

        try {
            $walletData = $this->publisherService->getWalletData($publisher);
            $paymentMethods = $this->publisherService->getPaymentMethods($publisher);
            
            Log::info('Wallet data retrieved successfully', [
                'user_id' => $publisher->id,
                'available_balance' => $walletData['availableBalance'] ?? 0,
                'total_earnings' => $walletData['totalEarnings'] ?? 0,
                'payment_methods_count' => count($paymentMethods)
            ]);
            
            return view('publisher.wallet.index', array_merge($walletData, [
                'paymentMethods' => $paymentMethods
            ]));
        } catch (\Exception $e) {
            Log::error('Error loading wallet data', [
                'user_id' => $publisher->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('publisher.wallet.index', [
                'wallet' => $publisher->getOrCreateWallet(),
                'totalEarnings' => 0,
                'clickEarnings' => 0,
                'conversionEarnings' => 0,
                'availableBalance' => 0,
                'pendingBalance' => 0,
                'withdrawalFees' => [],
                'monthlyStats' => [],
                'withdrawalHistory' => [],
                'recentTransactions' => [],
                'paymentMethods' => []
            ])->with('error', 'Có lỗi xảy ra khi tải dữ liệu ví');
        }
    }

    /**
     * Lấy dữ liệu ví (API)
     */
    public function getWalletData()
    {
        $publisher = Auth::user();
        $walletData = $this->publisherService->getWalletData($publisher);
        
        return response()->json([
            'success' => true,
            'data' => $walletData
        ]);
    }

    /**
     * Sync wallet từ transactions (API)
     */
    public function syncWallet()
    {
        try {
            $publisher = Auth::user();
            $this->publisherService->syncWalletFromTransactions($publisher);
            
            // Lấy dữ liệu mới sau khi sync
            $walletData = $this->publisherService->getWalletData($publisher);
            
            return response()->json([
                'success' => true,
                'message' => 'Wallet đã được đồng bộ thành công',
                'data' => $walletData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đồng bộ wallet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kiểm tra có thể rút tiền không
     */
    public function checkWithdrawal(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100000|max:5000000'
        ]);

        $publisher = Auth::user();
        $result = $this->publisherService->canWithdraw($publisher, $request->amount);
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Lấy thống kê ví
     */
    public function getStats(Request $request)
    {
        $publisher = Auth::user();
        $period = $request->get('period', 'month'); // day, week, month, year
        
        $stats = $this->getStatsByPeriod($publisher, $period);
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Lấy lịch sử giao dịch
     */
    public function getTransactions(Request $request)
    {
        $publisher = Auth::user();
        $perPage = $request->get('per_page', 15);
        $type = $request->get('type'); // commission_earned, withdrawal, etc.
        
        $query = $publisher->transactions();
        
        if ($type) {
            $query->where('type', $type);
        }
        
        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Lấy biểu đồ thu nhập
     */
    public function getEarningsChart(Request $request)
    {
        $publisher = Auth::user();
        $period = $request->get('period', '30'); // 7, 30, 90, 365 days
        
        $chartData = $this->getEarningsChartData($publisher, (int)$period);
        
        return response()->json([
            'success' => true,
            'data' => $chartData
        ]);
    }

    /**
     * Cập nhật hold period (chỉ admin)
     */
    public function updateHoldPeriod(Request $request)
    {
        $request->validate([
            'publisher_id' => 'required|exists:users,id',
            'hold_period_days' => 'required|integer|min:1|max:365'
        ]);

        $publisher = User::findOrFail($request->publisher_id);
        $wallet = $publisher->getOrCreateWallet();
        
        $wallet->update([
            'hold_period_days' => $request->hold_period_days
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Hold period đã được cập nhật'
        ]);
    }

    /**
     * Lấy thống kê theo khoảng thời gian
     */
    private function getStatsByPeriod($publisher, string $period): array
    {
        $startDate = match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $endDate = match($period) {
            'day' => now()->endOfDay(),
            'week' => now()->endOfWeek(),
            'month' => now()->endOfMonth(),
            'year' => now()->endOfYear(),
            default => now()->endOfMonth()
        };

        return [
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'clicks' => $this->getClicksInPeriod($publisher, $startDate, $endDate),
            'conversions' => $this->getConversionsInPeriod($publisher, $startDate, $endDate),
            'commission' => $this->getCommissionInPeriod($publisher, $startDate, $endDate),
            'withdrawals' => $this->getWithdrawalsInPeriod($publisher, $startDate, $endDate),
        ];
    }

    /**
     * Lấy dữ liệu biểu đồ thu nhập
     */
    private function getEarningsChartData($publisher, int $days): array
    {
        $data = [];
        $labels = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();
            
            $commission = $this->getCommissionInPeriod($publisher, $startOfDay, $endOfDay);
            $clicks = $this->getClicksInPeriod($publisher, $startOfDay, $endOfDay);
            
            $data[] = [
                'date' => $date->toDateString(),
                'commission' => $commission,
                'clicks' => $clicks,
                'total' => $commission + $clicks
            ];
            
            $labels[] = $date->format('d/m');
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    // Helper methods
    private function getClicksInPeriod($publisher, $startDate, $endDate): int
    {
        return $publisher->affiliateLinks()
            ->join('clicks', 'affiliate_links.id', '=', 'clicks.affiliate_link_id')
            ->whereBetween('clicks.created_at', [$startDate, $endDate])
            ->count();
    }

    private function getConversionsInPeriod($publisher, $startDate, $endDate): int
    {
        return $publisher->conversions()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    private function getCommissionInPeriod($publisher, $startDate, $endDate): float
    {
        return $publisher->conversions()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('commission');
    }

    private function getWithdrawalsInPeriod($publisher, $startDate, $endDate): array
    {
        return $publisher->withdrawals()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('status')
            ->get()
            ->toArray();
    }
}
