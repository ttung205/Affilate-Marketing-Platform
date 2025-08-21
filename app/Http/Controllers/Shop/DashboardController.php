<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Conversion;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Middleware sẽ được xử lý trong routes
    }

    public function dashboard()
    {
        $shop = Auth::user();
        
        // Thống kê cơ bản
        $stats = [
            'today_revenue' => $this->getTodayRevenue($shop->id),
            'month_revenue' => $this->getMonthRevenue($shop->id),
            'today_orders' => $this->getTodayOrders($shop->id),
            'month_orders' => $this->getMonthOrders($shop->id),
            'total_products' => Product::where('user_id', $shop->id)->count(),
            'active_products' => Product::where('user_id', $shop->id)->where('is_active', true)->count(),
        ];

        // Đơn hàng gần đây
        $recent_orders = $this->getRecentOrders($shop->id, 5);

        // Top sản phẩm bán chạy
        $top_products = $this->getTopProducts($shop->id, 5);

        return view('shop.dashboard', compact('stats', 'recent_orders', 'top_products'));
    }

    private function getTodayRevenue($shopId)
    {
        // Lấy doanh thu hôm nay từ conversions
        return Conversion::whereHas('product', function($query) use ($shopId) {
            $query->where('user_id', $shopId);
        })
        ->whereDate('created_at', today())
        ->sum('amount');
    }

    private function getMonthRevenue($shopId)
    {
        // Lấy doanh thu tháng này
        return Conversion::whereHas('product', function($query) use ($shopId) {
            $query->where('user_id', $shopId);
        })
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->sum('amount');
    }

    private function getTodayOrders($shopId)
    {
        // Lấy số đơn hàng hôm nay
        return Conversion::whereHas('product', function($query) use ($shopId) {
            $query->where('user_id', $shopId);
        })
        ->whereDate('created_at', today())
        ->count();
    }

    private function getMonthOrders($shopId)
    {
        // Lấy số đơn hàng tháng này
        return Conversion::whereHas('product', function($query) use ($shopId) {
            $query->where('user_id', $shopId);
        })
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();
    }

    private function getRecentOrders($shopId, $limit = 5)
    {
        // Lấy đơn hàng gần đây
        return Conversion::whereHas('product', function($query) use ($shopId) {
            $query->where('user_id', $shopId);
        })
        ->with(['product', 'affiliateLink.publisher'])
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get();
    }

    private function getTopProducts($shopId, $limit = 5)
    {
        // Lấy top sản phẩm bán chạy
        return Product::where('user_id', $shopId)
            ->withCount(['conversions as total_orders'])
            ->withSum(['conversions as total_revenue'], 'amount')
            ->orderBy('total_orders', 'desc')
            ->limit($limit)
            ->get();
    }
}
