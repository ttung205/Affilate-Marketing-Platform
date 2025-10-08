@extends('components.dashboard.layout')

@section('title', 'Admin Dashboard - Affiliate Marketing')

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
                <div class="admin-stat-breakdown">
                    <span><i class="fas fa-user-tie"></i> Shops: {{ number_format($userStats['shops']) }}</span>
                    <span><i class="fas fa-bullhorn"></i> Publishers: {{ number_format($userStats['publishers']) }}</span>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon admin-bg-success">
                    <i class="fas fa-box"></i>
                </div>
                <div class="admin-stat-number">{{ number_format($stats['total_products']) }}</div>
                <div class="admin-stat-label">Tổng sản phẩm</div>
                <div class="admin-stat-breakdown">
                    <span class="text-success"><i class="fas fa-check-circle"></i> Active: {{ number_format($productStats['active_products']) }}</span>
                    <span class="text-muted"><i class="fas fa-times-circle"></i> Inactive: {{ number_format($productStats['inactive_products']) }}</span>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon admin-bg-warning">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
                <div class="admin-stat-number">{{ number_format($stats['total_clicks']) }}</div>
                <div class="admin-stat-label">Tổng lượt click</div>
                <div class="admin-stat-breakdown">
                    <span><i class="fas fa-shopping-cart"></i> Conversions: {{ number_format($stats['total_orders']) }}</span>
                    <span><i class="fas fa-percentage"></i> Rate: {{ $stats['total_clicks'] > 0 ? number_format(($stats['total_orders'] / $stats['total_clicks']) * 100, 2) : 0 }}%</span>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="admin-stat-icon admin-bg-info">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="admin-stat-number">{{ number_format($revenueStats['total_commission']) }}đ</div>
                <div class="admin-stat-label">Tổng hoa hồng</div>
                <div class="admin-stat-breakdown">
                    <span class="text-warning"><i class="fas fa-clock"></i> Pending: {{ number_format($revenueStats['pending_commission']) }}đ</span>
                    <span class="text-success"><i class="fas fa-check"></i> Approved: {{ number_format($revenueStats['approved_commission']) }}đ</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="admin-content-section">
        <div class="admin-section-header">
            <h2>Hoạt động gần đây</h2>
            <p>Theo dõi các hoạt động mới nhất trên hệ thống</p>
        </div>

        <div class="admin-activity-grid">
            <!-- Recent Users -->
            <div class="admin-activity-card">
                <div class="admin-activity-header">
                    <h3><i class="fas fa-user-plus"></i> Người dùng mới</h3>
                </div>
                <div class="admin-activity-list">
                    @forelse($recentUsers as $user)
                        <div class="admin-activity-item">
                            <div class="admin-activity-avatar">
                                @if($user->avatar)
                                    @if(filter_var($user->avatar, FILTER_VALIDATE_URL))
                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}">
                                    @else
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}">
                                    @endif
                                @else
                                    <i class="fas fa-user"></i>
                                @endif
                            </div>
                            <div class="admin-activity-info">
                                <div class="admin-activity-name">{{ $user->name }}</div>
                                <div class="admin-activity-meta">
                                    <span class="admin-badge admin-badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                                    <span class="admin-activity-time">{{ $user->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="admin-empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Chưa có người dùng nào</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Products -->
            <div class="admin-activity-card">
                <div class="admin-activity-header">
                    <h3><i class="fas fa-box"></i> Sản phẩm mới</h3>
                </div>
                <div class="admin-activity-list">
                    @forelse($recentProducts as $product)
                        <div class="admin-activity-item">
                            <div class="admin-activity-avatar">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                                @else
                                    <i class="fas fa-box"></i>
                                @endif
                            </div>
                            <div class="admin-activity-info">
                                <div class="admin-activity-name">{{ Str::limit($product->name, 30) }}</div>
                                <div class="admin-activity-meta">
                                    <span class="admin-activity-price">{{ number_format($product->price) }}đ</span>
                                    <span class="admin-activity-time">{{ $product->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="admin-empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Chưa có sản phẩm nào</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Conversions -->
            <div class="admin-activity-card">
                <div class="admin-activity-header">
                    <h3><i class="fas fa-shopping-cart"></i> Conversion gần đây</h3>
                </div>
                <div class="admin-activity-list">
                    @forelse($recentConversions as $conversion)
                        <div class="admin-activity-item">
                            <div class="admin-activity-avatar admin-avatar-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="admin-activity-info">
                                <div class="admin-activity-name">{{ Str::limit($conversion->affiliateLink->product->name ?? 'N/A', 30) }}</div>
                                <div class="admin-activity-meta">
                                    <span class="admin-activity-commission">{{ number_format($conversion->commission) }}đ</span>
                                    <span class="admin-activity-time">{{ $conversion->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="admin-empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Chưa có conversion nào</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Completed Withdrawals Section -->
        <div class="admin-content-section">
            <div class="admin-section-header">
                <h2>Thống kê rút tiền đã hoàn thành</h2>
            </div>

            <div class="admin-withdrawals-table">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Publisher</th>
                            <th>Số tiền</th>
                            <th>Phí</th>
                            <th>Thực nhận</th>
                            <th>Phương thức</th>
                            <th>Người xử lý</th>
                            <th>Ngày hoàn thành</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($completedWithdrawals as $withdrawal)
                            <tr>
                                <td>
                                    <div class="admin-user-cell">
                                        <div class="admin-user-avatar">
                                            @if($withdrawal->publisher->avatar)
                                                @if(filter_var($withdrawal->publisher->avatar, FILTER_VALIDATE_URL))
                                                    <img src="{{ $withdrawal->publisher->avatar }}" alt="{{ $withdrawal->publisher->name }}">
                                                @else
                                                    <img src="{{ asset('storage/' . $withdrawal->publisher->avatar) }}" alt="{{ $withdrawal->publisher->name }}">
                                                @endif
                                            @else
                                                <i class="fas fa-user"></i>
                                            @endif
                                        </div>
                                        <div class="admin-user-info">
                                            <div class="admin-user-name">{{ $withdrawal->publisher->name }}</div>
                                            <div class="admin-user-email">{{ $withdrawal->publisher->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="admin-amount-text">{{ number_format($withdrawal->amount) }}đ</span></td>
                                <td><span class="admin-fee-text">{{ number_format($withdrawal->fee) }}đ</span></td>
                                <td><span class="admin-net-amount-text">{{ number_format($withdrawal->net_amount) }}đ</span></td>
                                <td><span class="admin-badge admin-badge-info">{{ $withdrawal->payment_method_label }}</span></td>
                                <td>
                                    @if($withdrawal->processedBy)
                                        <div class="admin-processor-cell">
                                            <div class="admin-processor-avatar">
                                                @if($withdrawal->processedBy->avatar)
                                                    @if(filter_var($withdrawal->processedBy->avatar, FILTER_VALIDATE_URL))
                                                        <img src="{{ $withdrawal->processedBy->avatar }}" alt="{{ $withdrawal->processedBy->name }}">
                                                    @else
                                                        <img src="{{ asset('storage/' . $withdrawal->processedBy->avatar) }}" alt="{{ $withdrawal->processedBy->name }}">
                                                    @endif
                                                @else
                                                    <i class="fas fa-user-shield"></i>
                                                @endif
                                            </div>
                                            <span>{{ $withdrawal->processedBy->name }}</span>
                                        </div>
                                    @else
                                        <span class="admin-text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="admin-date-text">{{ $withdrawal->completed_at->format('d/m/Y H:i') }}</span>
                                    <small class="admin-time-ago">{{ $withdrawal->completed_at->diffForHumans() }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="admin-empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Chưa có giao dịch rút tiền hoàn thành nào</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection