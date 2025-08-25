@extends('publisher.layouts.app')

@section('title', 'Affiliate Links - Publisher Dashboard')

@section('content')
<div class="publisher-content">
    <!-- Header -->
    <div class="content-header">
        <div class="header-left">
            <h1>Affiliate Links</h1>
            <p>Quản lý affiliate links của bạn</p>
        </div>
        <div class="header-right">
            <a href="{{ route('publisher.affiliate-links.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Tạo Link Mới
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-link"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['total'] ?? 0 }}</h3>
                <p>Tổng Links</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3>{{ $stats['active'] ?? 0 }}</h3>
                <p>Đang hoạt động</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-mouse-pointer"></i>
            </div>
            <div class="stat-content">
                <h3>{{ number_format($stats['total_clicks'] ?? 0) }}</h3>
                <p>Tổng Clicks</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <h3>{{ number_format($stats['total_conversions'] ?? 0) }}</h3>
                <p>Conversions</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label>Sản phẩm:</label>
                <select name="product_id" class="form-select">
                    <option value="">Tất cả sản phẩm</option>
                    @foreach($products ?? [] as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label></label>Campaign:</label>
                <select name="campaign_id" class="form-select">
                    <option value="">Tất cả campaigns</option>
                    @foreach($campaigns ?? [] as $campaign)
                        <option value="{{ $campaign->id }}" {{ request('campaign_id') == $campaign->id ? 'selected' : '' }}>
                            {{ $campaign->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Trạng thái:</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Vô hiệu hóa</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Tìm kiếm:</label>
                <input type="text" name="search" class="form-control" placeholder="Tracking code..." value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-secondary">Lọc</button>
          
        </form>
    </div>

    <!-- Affiliate Links Table -->
    <div class="table-section">
        @if(($affiliateLinks->count() ?? 0) > 0)
            <div class="table-responsive">
                <table class="affiliate-links-table">
                    <thead>
                        <tr>
                            <th>URL đích</th>
                            <th>Campaign</th>
                            <th>Links & Tracking</th>
                            <th>Commission</th>
                            <th>Thống kê</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($affiliateLinks ?? [] as $link)
                            <tr>
                                <td class="product-cell">
                                    <div class="product-info">
                                        @if($link->product)
                                            @if($link->product->image)
                                                <img src="{{ get_image_url($link->product->image) }}" alt="{{ $link->product->name }}" class="product-thumb">
                                            @endif
                                            <div class="product-details">
                                                <h4>{{ $link->product->name }}</h4>
                                                <p>{{ $link->product->category->name ?? 'N/A' }}</p>
                                            </div>
                                        @else
                                            <div class="product-details">
                                                <h4>Link tự tạo</h4>
                                                <p class="text-muted">{{ Str::limit($link->original_url, 40) }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="campaign-cell">
                                    @if($link->campaign)
                                        <span class="campaign-badge">{{ $link->campaign->name }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="links-cell">
                                    <div class="link-item">
                                        <label>Short URL:</label>
                                        <div class="link-group">
                                            <input type="text" value="{{ route('tracking.short', $link->short_code) }}" readonly class="form-control link-input">
                                            <button class="btn btn-sm btn-outline-primary copy-btn" onclick="copyToClipboard('{{ route('tracking.short', $link->short_code) }}')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="link-item">
                                        <label>Tracking Code:</label>
                                        <code>{{ $link->tracking_code }}</code>
                                    </div>
                                </td>
                                <td class="commission-cell">
                                    <div class="commission-rate-publisher">{{ $link->commission_rate }}%</div>
                                    <div class="commission-earned">
                                        Earned: {{ number_format($link->total_commission) }} VNĐ
                                    </div>
                                </td>
                                <td class="stats-cell">
                                    <div class="stats-grid">
                                        <div class="stat-item">
                                            <span class="stat-value">{{ $link->total_clicks }}</span>
                                            <span class="stat-label">Clicks</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-value">{{ $link->total_conversions }}</span>
                                            <span class="stat-label">Conv.</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-value">{{ $link->conversion_rate }}%</span>
                                            <span class="stat-label">Rate</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="status-cell">
                                    @if($link->status === 'active')
                                        <span class="status-badge status-active">Đang hoạt động</span>
                                    @elseif($link->status === 'inactive')
                                        <span class="status-badge status-inactive">Vô hiệu hóa</span>
                                    @else
                                        <span class="status-badge status-pending">Chờ duyệt</span>
                                    @endif
                                </td>
                                <td class="actions-cell">
                                    <div class="action-buttons">
                                        <a href="{{ route('publisher.affiliate-links.show', $link) }}" class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('publisher.affiliate-links.edit', $link) }}" class="btn btn-sm btn-outline-secondary" title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(!$link->clicks()->exists() && !$link->conversions()->exists())
                                            <form method="POST" action="{{ route('publisher.affiliate-links.destroy', $link) }}" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa link này?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $affiliateLinks->appends(request()->query())->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-link fa-3x"></i>
                </div>
                <h3>Chưa có affiliate link nào</h3>
                <p>Bắt đầu tạo affiliate link đầu tiên của bạn để kiếm commission!</p>
                <a href="{{ route('publisher.affiliate-links.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Tạo Link Đầu Tiên
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'toast success';
        toast.textContent = 'Đã copy vào clipboard!';
        document.body.appendChild(toast);
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 3000);
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
    });
}
</script>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/publisher/affiliate-links.css') }}">
@endpush
