<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Click;
use App\Models\Conversion;
use App\Models\Withdrawal;

class DashboardController extends Controller
{
    public function index()
    {
        // Lấy thống kê cơ bản từ database
        $stats = [
            'total_users' => User::count(),
            'total_products' => Product::count(), 
            'total_clicks' => Click::count(), 
            'total_orders' => Conversion::count(), 
        ];

        // Thống kê chi tiết người dùng
        $userStats = [
            'publishers' => User::where('role', 'publisher')->count(),
            'shops' => User::where('role', 'shop')->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];

        // Thống kê sản phẩm
        $productStats = [
            'active_products' => Product::where('is_active', true)->count(),
            'inactive_products' => Product::where('is_active', false)->count(),
        ];

        // Thống kê doanh thu
        $revenueStats = [
            'total_commission' => Conversion::sum('commission'),
            'pending_commission' => Conversion::where('status', 'pending')->sum('commission'),
            'approved_commission' => Conversion::where('status', 'approved')->sum('commission'),
        ];

        // Người dùng mới gần đây
        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();

        // Sản phẩm mới nhất
        $recentProducts = Product::with('shopOwner')->orderBy('created_at', 'desc')->take(5)->get();

        // Conversion gần đây
        $recentConversions = Conversion::with(['affiliateLink.product', 'affiliateLink.publisher'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Rút tiền đã hoàn thành
        $completedWithdrawals = Withdrawal::with(['publisher', 'processedBy'])
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->take(10)
            ->get();
        
        return view('admin.dashboard', compact('stats', 'userStats', 'productStats', 'revenueStats', 'recentUsers', 'recentProducts', 'recentConversions', 'completedWithdrawals'));
    }
}