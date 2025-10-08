@extends('publisher.layouts.app')

@section('title', 'Publisher Dashboard - Affiliate Marketing')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('publisher.dashboard') }}" class="breadcrumb-link">
            <i class="fas fa-home"></i>
            <span>Publisher</span>
        </a>
    </li>
    <li class="breadcrumb-item">
        <i class="fas fa-chevron-right breadcrumb-arrow"></i>
    </li>
    <li class="breadcrumb-item active">
        <span>Dashboard</span>
    </li>
@endsection

@section('content')
    <div class="publisher-dashboard">
        <!-- Header -->
        <div class="dashboard-header">
            <h1>Publisher Dashboard</h1>
            <p>Chào mừng bạn trở lại, {{ Auth::user()->name }}!</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <!-- Wallet Info -->
            <div class="stat-card wallet-card">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-content">
                    <h3>Số dư khả dụng</h3>
                    <p class="stat-value">{{ number_format($walletData['wallet']->balance ?? 0) }} VNĐ</p>
                    <small class="stat-breakdown">
                        Chờ xử lý: {{ number_format($walletData['wallet']->pending_balance ?? 0) }} VNĐ
                    </small>
                    <div class="wallet-actions">
                        <a href="{{ route('publisher.wallet.index') }}" class="btn btn-sm btn-primary">Xem ví</a>
                        <a href="{{ route('publisher.withdrawal.index') }}" class="btn btn-sm btn-outline-primary">Rút
                            tiền</a>
                    </div>
                </div>
            </div>

            <!-- Publisher Ranking -->
            <div class="stat-card ranking-card" style=" {{ Auth::user()->ranking_color ?? '#6B7280' }}">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <h3>Hạng hiện tại</h3>
                    <p class="stat-value" style="color: {{ Auth::user()->ranking_color ?? '#6B7280' }}">
                        {{ Auth::user()->ranking_name ?? 'Chưa có hạng' }}
                    </p>
                    <small class="stat-breakdown">
                        {{ Auth::user()->affiliateLinks()->count() }} links |
                        {{ number_format(Auth::user()->getCombinedCommissionAttribute(), 0, ',', '.') }} VNĐ
                    </small>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
                <div class="stat-content">
                    <h3>Tổng lượt click</h3>
                    <p class="stat-value">{{ number_format($stats['total_clicks']) }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <h3>Tổng đơn hàng</h3>
                    <p class="stat-value">{{ number_format($stats['total_conversions']) }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>Tổng hoa hồng</h3>
                    <p class="stat-value">{{ number_format($stats['combined_commission']) }} VNĐ</p>
                    <small class="stat-breakdown">
                        Click: {{ number_format($stats['click_commission']) }} VNĐ |
                        Conversion: {{ number_format($stats['total_commission']) }} VNĐ
                    </small>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-content">
                    <h3>Tỷ lệ chuyển đổi</h3>
                    <p class="stat-value">{{ $stats['conversion_rate'] }}%</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-link"></i>
                </div>
                <div class="stat-content">
                    <h3>Links đang hoạt động</h3>
                    <p class="stat-value">{{ number_format($stats['active_links']) }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <h3>Sản phẩm đang quảng bá</h3>
                    <p class="stat-value">{{ number_format($stats['total_products']) }}</p>
                </div>
            </div>
        </div>

        <!-- Ranking Progress Widget -->
        @if(Auth::user()->publisherRanking)
            <div class="ranking-progress-section">
                <div class="content-card">
                    <div class="card-header">
                        <h2>🎯 Tiến độ lên hạng tiếp theo</h2>
                        <a href="{{ route('publisher.ranking.index') }}" class="view-all-btn">Xem chi tiết</a>
                    </div>
                    <div class="card-content">
                        @php
                            $progress = Auth::user()->getRankingProgressAttribute();
                        @endphp
                        @if(!$progress['is_max_level'])
                            <div class="ranking-progress">
                                <div class="progress-info">
                                    <div class="next-ranking">
                                        <span class="ranking-icon">{{ $progress['next_ranking']->icon ?? '🏆' }}</span>
                                        <span class="ranking-name">{{ $progress['next_ranking']->name ?? 'Hạng tiếp theo' }}</span>
                                    </div>
                                    <div class="progress-stats">
                                        <div class="stat">
                                            <span class="label">Links:</span>
                                            <span
                                                class="value">{{ $progress['stats']['total_links'] }}/{{ $progress['next_ranking']->min_links }}</span>
                                        </div>
                                        <div class="stat">
                                            <span class="label">Hoa hồng:</span>
                                            <span
                                                class="value">{{ number_format($progress['stats']['total_commission'], 0, ',', '.') }}/{{ number_format($progress['next_ranking']->min_commission, 0, ',', '.') }}
                                                VNĐ</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress-bars">
                                    <div class="progress-item">
                                        <div class="progress-label">
                                            <span>📎 Số Link</span>
                                            <span>{{ $progress['progress']['links_progress'] }}%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: {{ $progress['progress']['links_progress'] }}%">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress-item">
                                        <div class="progress-label">
                                            <span>💰 Hoa Hồng</span>
                                            <span>{{ $progress['progress']['commission_progress'] }}%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success"
                                                style="width: {{ $progress['progress']['commission_progress'] }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="max-ranking">
                                <div class="max-ranking-content">
                                    <div class="max-ranking-icon">👑</div>
                                    <h3>Chúc mừng!</h3>
                                    <p>Bạn đã đạt hạng cao nhất trong hệ thống!</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Time-based Stats -->
        <div class="time-stats-section">
            <h2>Thống kê theo thời gian</h2>
            <div class="time-stats-grid">
                <div class="time-stat-card">
                    <h4>Hôm nay</h4>
                    <div class="time-stat-content">
                        <div class="time-stat-item">
                            <span class="label">Clicks:</span>
                            <span class="value">{{ number_format($timeStats['today_clicks']) }}</span>
                        </div>
                        <div class="time-stat-item">
                            <span class="label">Đơn hàng:</span>
                            <span class="value">{{ number_format($timeStats['today_conversions']) }}</span>
                        </div>
                        <div class="time-stat-item">
                            <span class="label">Hoa hồng:</span>
                            <span class="value">{{ number_format($timeStats['today_commission']) }} VNĐ</span>
                        </div>
                    </div>
                </div>

                <div class="time-stat-card">
                    <h4>Tháng này</h4>
                    <div class="time-stat-content">
                        <div class="time-stat-item">
                            <span class="label">Clicks:</span>
                            <span class="value">{{ number_format($timeStats['month_clicks']) }}</span>
                        </div>
                        <div class="time-stat-item">
                            <span class="label">Đơn hàng:</span>
                            <span class="value">{{ number_format($timeStats['month_conversions']) }}</span>
                        </div>
                        <div class="time-stat-item">
                            <span class="label">Hoa hồng:</span>
                            <span class="value">{{ number_format($timeStats['month_commission']) }} VNĐ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="dashboard-content">
            <!-- Performance Chart -->
            <div class="content-card chart-card">
                <div class="card-header">
                    <h2>Hiệu suất 7 ngày gần đây</h2>
                </div>
                <div class="card-content">
                    <canvas id="performanceChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Top Products -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Top sản phẩm hiệu suất cao</h2>
                    <a href="#" class="view-all-btn">Xem tất cả</a>
                </div>
                <div class="card-content">
                    @if($topProducts->count() > 0)
                        <div class="products-list">
                            @foreach($topProducts as $product)
                                <div class="product-item">
                                    <div class="product-info">
                                        <img src="{{ get_image_url($product->image) }}" alt="{{ $product->name }}"
                                            class="product-thumb">
                                        <div class="product-details">
                                            <h4>{{ $product->name }}</h4>
                                            <p class="category">{{ $product->category->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="product-stats">
                                        <div class="stat">
                                            <span class="label">Clicks:</span>
                                            <span class="value">{{ $product->total_clicks ?? 0 }}</span>
                                        </div>
                                        <div class="stat">
                                            <span class="label">Đơn hàng:</span>
                                            <span class="value">{{ $product->total_conversions ?? 0 }}</span>
                                        </div>
                                        <div class="stat">
                                            <span class="label">Hoa hồng:</span>
                                            <span class="value">{{ number_format($product->total_commission ?? 0) }} VNĐ</span>
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

            <!-- Recent Conversions -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Đơn hàng gần đây</h2>
                    <a href="#" class="view-all-btn">Xem tất cả</a>
                </div>
                <div class="card-content">
                    @if($recentConversions->count() > 0)
                        <div class="conversions-list">
                            @foreach($recentConversions as $conversion)
                                <div class="conversion-item">
                                    <div class="conversion-info">
                                        <div class="conversion-product">
                                            <img src="{{ get_image_url($conversion->product->image) }}"
                                                alt="{{ $conversion->product->name }}" class="product-thumb">
                                            <div class="product-details">
                                                <h4>{{ $conversion->product->name }}</h4>
                                                <p class="order-id">Order ID: #{{ $conversion->id }}</p>
                                            </div>
                                        </div>
                                        <div class="conversion-amount">
                                            <span class="amount">{{ number_format($conversion->amount) }} VNĐ</span>
                                            <span class="commission">Hoa hồng: {{ number_format($conversion->commission) }}
                                                VNĐ</span>
                                            <span class="date">{{ $conversion->created_at->format('d/m/Y H:i') }}</span>
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

            <!-- Recent Affiliate Links -->
            <div class="content-card">
                <div class="card-header">
                    <h2>Affiliate Links gần đây</h2>
                    <a href="#" class="view-all-btn">Xem tất cả</a>
                </div>
                <div class="card-content">
                    @if($recentLinks->count() > 0)
                        <div class="links-list">
                            @foreach($recentLinks as $link)
                                <div class="link-item">
                                    <div class="link-info">
                                        <div class="link-product">
                                            @if($link->product)
                                                <img src="{{ get_image_url($link->product->image) }}" alt="{{ $link->product->name }}"
                                                    class="product-thumb">
                                                <div class="product-details">
                                                    <h4>{{ $link->product->name }}</h4>
                                                    <p class="campaign">{{ $link->campaign->name ?? 'N/A' }}</p>
                                                </div>
                                            @else
                                                <div class="custom-link-icon">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </div>
                                                <div class="product-details">
                                                    <h4>Link tự tạo</h4>
                                                    <p class="campaign">{{ $link->campaign->name ?? 'N/A' }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="link-details">
                                            <div class="link-url">
                                                <span class="label">Link:</span>
                                                <a href="{{ $link->full_url }}" target="_blank"
                                                    class="url">{{ $link->tracking_code }}</a>
                                            </div>
                                            <div class="link-stats">
                                                <span class="clicks">{{ $link->total_clicks }} clicks</span>
                                                <span class="conversions">{{ $link->total_conversions }} orders</span>
                                            </div>
                                            <span class="date">{{ $link->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-link"></i>
                            <p>Chưa có affiliate link nào</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/publisher/dashboard.css') }}">
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Performance Chart
            const ctx = document.getElementById('performanceChart').getContext('2d');
            const chartData = @json($chartData);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Clicks',
                            data: chartData.clicks,
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Đơn hàng',
                            data: chartData.conversions,
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Hoa hồng (K VNĐ)',
                            data: chartData.commissions.map(c => c / 1000),
                            borderColor: '#F59E0B',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            tension: 0.4,
                            fill: true,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Ngày'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Số lượng'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Hoa hồng (K VNĐ)'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
@endpush