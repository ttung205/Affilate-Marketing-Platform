@extends('components.dashboard.layout')

@section('title', 'Admin Dashboard - TTung Affiliate')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endpush

@section('content')
<div class="admin-dashboard-content">
    <!-- System Overview Section -->
    <div class="admin-content-section">
        <div class="admin-section-header">
            <h2>Tổng quan hệ thống</h2>
            <p>Xem thống kê và phân tích dữ liệu</p>
        </div>
        
        <div class="admin-stats-grid">
            <div class="admin-stat-card">
                <div class="admin-stat-icon admin-bg-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="admin-stat-number">{{ number_format($stats['total_users']) }}</div>
                <div class="admin-stat-label">Tổng người dùng</div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon admin-bg-success">
                    <i class="fas fa-box"></i>
                </div>
                <div class="admin-stat-number">{{ number_format($stats['total_products']) }}</div>
                <div class="admin-stat-label">Tổng sản phẩm</div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon admin-bg-warning">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
                <div class="admin-stat-number">{{ number_format($stats['total_clicks']) }}</div>
                <div class="admin-stat-label">Tổng lượt click</div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon admin-bg-info">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="admin-stat-number">{{ number_format($stats['total_orders']) }}</div>
                <div class="admin-stat-label">Tổng đơn hàng</div>
            </div>
        </div>
    </div>
</div>
@endsection