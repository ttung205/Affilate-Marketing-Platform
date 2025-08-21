<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Click;
use App\Models\Conversion;

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
        
        return view('admin.dashboard', compact('stats'));
    }
}