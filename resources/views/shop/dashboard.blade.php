@extends('shop.layouts.app')

@section('title', 'Shop Dashboard')

@section('breadcrumb')
<li class="breadcrumb-item">
    <i class="fas fa-chevron-right breadcrumb-arrow"></i>
</li>
<li class="breadcrumb-item active">
    <span>Dashboard</span>
</li>
@endsection

@section('content')
<div class="shop-dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <h1>Shop Dashboard</h1>
        <p>Chào mừng bạn trở lại, {{ Auth::user()->name }}!</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <h3>Doanh thu hôm nay</h3>
                <p class="stat-value">{{ number_format($stats['today_revenue']) }} VNĐ</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <h3>Doanh thu tháng</h3>
                <p class="stat-value">{{ number_format($stats['month_revenue']) }} VNĐ</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <h3>Đơn hàng hôm nay</h3>
                <p class="stat-value">{{ $stats['today_orders'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3>Đơn hàng tháng</h3>
                <p class="stat-value">{{ $stats['month_orders'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-history"></i>
            </div>
            <div class="stat-content">
                <h3>Đơn chờ duyệt</h3>
                <p class="stat-value">{{ $stats['pending_conversions'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-content">
                <h3>Tổng sản phẩm</h3>
                <p class="stat-value">{{ $stats['total_products'] }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3>Sản phẩm hoạt động</h3>
                <p class="stat-value">{{ $stats['active_products'] }}</p>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="dashboard-content">
        <!-- Recent Orders -->
        <div class="content-card">
            <div class="card-header">
                <h2>Đơn hàng gần đây</h2>
                <a href="#" class="view-all-btn">Xem tất cả</a>
            </div>
            <div class="card-content">
                @if($recent_orders->count() > 0)
                    <div class="orders-list">
                        @foreach($recent_orders as $order)
                        <div class="order-item">
                            <div class="order-info">
                                <div class="order-product">
                                    <img src="{{ get_image_url($order->product->image) }}" alt="{{ $order->product->name }}" class="product-thumb">
                                    <div class="product-details">
                                        <h4>{{ $order->product->name }}</h4>
                                        <p class="publisher">Publisher: {{ $order->affiliateLink->publisher->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="order-amount">
                                    <span class="amount">{{ number_format($order->amount) }} VNĐ</span>
                                    <span class="date">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Chưa có đơn hàng nào</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Top Products -->
        <div class="content-card">
            <div class="card-header">
                <h2>Top sản phẩm bán chạy</h2>
                <a href="#" class="view-all-btn">Xem tất cả</a>
            </div>
            <div class="card-content">
                @if($top_products->count() > 0)
                    <div class="products-list">
                        @foreach($top_products as $product)
                        <div class="product-item">
                            <div class="product-info">
                                <img src="{{ get_image_url($product->image) }}" alt="{{ $product->name }}" class="product-thumb">
                                <div class="product-details">
                                    <h4>{{ $product->name }}</h4>
                                    <p class="category">{{ $product->category->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="product-stats">
                                <div class="stat">
                                    <span class="label">Đơn hàng:</span>
                                    <span class="value">{{ $product->total_orders ?? 0 }}</span>
                                </div>
                                <div class="stat">
                                    <span class="label">Doanh thu:</span>
                                    <span class="value">{{ number_format($product->total_revenue ?? 0) }} VNĐ</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>Chưa có sản phẩm nào</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/shop/dashboard.css') }}">
@endpush