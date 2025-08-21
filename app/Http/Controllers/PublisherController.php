<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Product;
use App\Models\Click;
use App\Models\Conversion;
use App\Models\AffiliateLink;
use App\Models\Campaign;

class PublisherController extends Controller
{
    public function __construct()
    {
       
    }

    public function dashboard()
    {
        $publisher = Auth::user();
        
        // Thống kê cơ bản
        $stats = [
            'total_clicks' => $this->getTotalClicks($publisher->id),
            'total_conversions' => $this->getTotalConversions($publisher->id),
            'total_commission' => $this->getTotalCommission($publisher->id),
            'conversion_rate' => $this->getConversionRate($publisher->id),
            'active_links' => $this->getActiveLinks($publisher->id),
            'total_products' => $this->getTotalProducts($publisher->id),
        ];

        // Thống kê theo thời gian
        $timeStats = [
            'today_clicks' => $this->getTodayClicks($publisher->id),
            'today_conversions' => $this->getTodayConversions($publisher->id),
            'today_commission' => $this->getTodayCommission($publisher->id),
            'month_clicks' => $this->getMonthClicks($publisher->id),
            'month_conversions' => $this->getMonthConversions($publisher->id),
            'month_commission' => $this->getMonthCommission($publisher->id),
        ];

        // Top sản phẩm hiệu suất cao
        $topProducts = $this->getTopProducts($publisher->id, 5);

        // Đơn hàng gần đây
        $recentConversions = $this->getRecentConversions($publisher->id, 5);

        // Affiliate links gần đây
        $recentLinks = $this->getRecentLinks($publisher->id, 5);

        // Dữ liệu cho biểu đồ
        $chartData = $this->getChartData($publisher->id);

        return view('publisher.dashboard', compact(
            'stats', 
            'timeStats', 
            'topProducts', 
            'recentConversions', 
            'recentLinks',
            'chartData'
        ));
    }

    private function getTotalClicks($publisherId)
    {
        return Click::where('publisher_id', $publisherId)->count();
    }

    private function getTotalConversions($publisherId)
    {
        return Conversion::where('publisher_id', $publisherId)->count();
    }

    private function getTotalCommission($publisherId)
    {
        return Conversion::where('publisher_id', $publisherId)->sum('commission');
    }

    private function getConversionRate($publisherId)
    {
        $clicks = $this->getTotalClicks($publisherId);
        if ($clicks === 0) return 0;
        
        $conversions = $this->getTotalConversions($publisherId);
        return round(($conversions / $clicks) * 100, 2);
    }

    private function getActiveLinks($publisherId)
    {
        return AffiliateLink::where('publisher_id', $publisherId)
            ->where('status', 'active')
            ->count();
    }

    private function getTotalProducts($publisherId)
    {
        return AffiliateLink::where('publisher_id', $publisherId)
            ->distinct('product_id')
            ->count();
    }

    private function getTodayClicks($publisherId)
    {
        return Click::where('publisher_id', $publisherId)
            ->whereDate('created_at', today())
            ->count();
    }

    private function getTodayConversions($publisherId)
    {
        return Conversion::where('publisher_id', $publisherId)
            ->whereDate('created_at', today())
            ->count();
    }

    private function getTodayCommission($publisherId)
    {
        return Conversion::where('publisher_id', $publisherId)
            ->whereDate('created_at', today())
            ->sum('commission');
    }

    private function getMonthClicks($publisherId)
    {
        return Click::where('publisher_id', $publisherId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    private function getMonthConversions($publisherId)
    {
        return Conversion::where('publisher_id', $publisherId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    private function getMonthCommission($publisherId)
    {
        return Conversion::where('publisher_id', $publisherId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('commission');
    }

    private function getTopProducts($publisherId, $limit = 5)
    {
        return Product::whereHas('affiliateLinks', function($query) use ($publisherId) {
            $query->where('publisher_id', $publisherId);
        })
        ->withCount(['clicks as total_clicks' => function($query) use ($publisherId) {
            $query->where('publisher_id', $publisherId);
        }])
        ->withCount(['conversions as total_conversions' => function($query) use ($publisherId) {
            $query->where('publisher_id', $publisherId);
        }])
        ->withSum(['conversions as total_commission' => function($query) use ($publisherId) {
            $query->where('publisher_id', $publisherId);
        }], 'commission')
        ->orderBy('total_conversions', 'desc')
        ->limit($limit)
        ->get();
    }

    private function getRecentConversions($publisherId, $limit = 5)
    {
        return Conversion::where('publisher_id', $publisherId)
            ->with(['product', 'affiliateLink'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getRecentLinks($publisherId, $limit = 5)
    {
        return AffiliateLink::where('publisher_id', $publisherId)
            ->with(['product', 'campaign'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getChartData($publisherId)
    {
        // Dữ liệu 7 ngày gần đây
        $days = collect();
        $clicks = collect();
        $conversions = collect();
        $commissions = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days->push($date->format('d/m'));
            
            $dayClicks = Click::where('publisher_id', $publisherId)
                ->whereDate('created_at', $date)
                ->count();
            $clicks->push($dayClicks);
            
            $dayConversions = Conversion::where('publisher_id', $publisherId)
                ->whereDate('created_at', $date)
                ->count();
            $conversions->push($dayConversions);
            
            $dayCommission = Conversion::where('publisher_id', $publisherId)
                ->whereDate('created_at', $date)
                ->sum('commission');
            $commissions->push($dayCommission);
        }

        return [
            'labels' => $days->toArray(),
            'clicks' => $clicks->toArray(),
            'conversions' => $conversions->toArray(),
            'commissions' => $commissions->toArray(),
        ];
    }
}
