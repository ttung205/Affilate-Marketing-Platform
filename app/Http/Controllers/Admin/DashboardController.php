<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        // Lấy thống kê cơ bản từ User table
        $stats = [
            'total_users' => User::count(),
            'total_products' => Product::count(), 
            'total_clicks' => 0,   
            'total_orders' => 0,   
        ];
        
        return view('admin.dashboard', compact('stats'));
    }
}