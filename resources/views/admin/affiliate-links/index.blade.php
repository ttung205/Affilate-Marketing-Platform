@extends('components.dashboard.layout')

@section('title', 'Quản lý Affiliate Links')

@section('content')
<div class="affiliate-links-container">
    <!-- Header Section -->
    <div class="affiliate-header">
        <div class="affiliate-header-left">
            <h1 class="affiliate-title">Quản lý Affiliate Links</h1>
            <p class="affiliate-description">Quản lý tất cả affiliate links trong hệ thống</p>
        </div>
        <div class="affiliate-header-right">
            <a href="{{ route('admin.affiliate-links.create') }}" class="affiliate-btn affiliate-btn-primary">
                <i class="fas fa-plus"></i>
                <span>Tạo Affiliate Link</span>
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="affiliate-stats-grid">
        <div class="affiliate-stat-card affiliate-stat-primary">
            <div class="affiliate-stat-icon">
                <i class="fas fa-link"></i>
            </div>
            <div class="affiliate-stat-content">
                <h3>{{ $stats['total'] ?? 0 }}</h3>
                <p>Tổng Links</p>
            </div>
        </div>

        <div class="affiliate-stat-card affiliate-stat-success">
            <div class="affiliate-stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="affiliate-stat-content">
                <h3>{{ $stats['active'] ?? 0 }}</h3>
                <p>Đang hoạt động</p>
            </div>
        </div>

        <div class="affiliate-stat-card affiliate-stat-warning">
            <div class="affiliate-stat-icon">
                <i class="fas fa-mouse-pointer"></i>
            </div>
            <div class="affiliate-stat-content">
                <h3>{{ number_format($stats['total_clicks'] ?? 0) }}</h3>
                <p>Tổng Clicks</p>
            </div>
        </div>

        <div class="affiliate-stat-card affiliate-stat-info">
            <div class="affiliate-stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="affiliate-stat-content">
                <h3>{{ number_format($stats['total_conversions'] ?? 0) }}</h3>
                <p>Tổng Conversions</p>
            </div>
        </div>

        <div class="affiliate-stat-card affiliate-stat-primary">
            <div class="affiliate-stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="affiliate-stat-content">
                <h3>{{ number_format($stats['total_revenue'] ?? 0) }} VNĐ</h3>
                <p>Tổng Doanh thu</p>
            </div>
        </div>

        <div class="affiliate-stat-card affiliate-stat-secondary">
            <div class="affiliate-stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="affiliate-stat-content">
                <h3>{{ $stats['pending'] ?? 0 }}</h3>
                <p>Chờ duyệt</p>
            </div>
        </div>

        <div class="affiliate-stat-card affiliate-stat-danger">
            <div class="affiliate-stat-icon">
                <i class="fas fa-ban"></i>
            </div>
            <div class="affiliate-stat-content">
                <h3>{{ $stats['inactive'] ?? 0 }}</h3>
                <p>Vô hiệu hóa</p>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="affiliate-filters-card">
        <div class="filters-header">
            <h3 class="filters-title">Bộ lọc & Tìm kiếm</h3>
        </div>
        <div class="filters-body">
            <form method="GET" action="{{ route('admin.affiliate-links.index') }}" class="filters-form">
                <div class="filters-row">
                    <div class="filter-group">
                        <label for="search" class="filter-label">Tìm kiếm</label>
                        <input type="text" class="filter-input" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Tracking code, URL...">
                    </div>
                    
                    <div class="filter-group">
                        <label for="status" class="filter-label">Trạng thái</label>
                        <select class="filter-select" id="status" name="status">
                            <option value="">Tất cả</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Vô hiệu hóa</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        </select>
                    </div>
                    
                                         <div class="filter-group">
                         <label for="publisher" class="filter-label">Publisher</label>
                         <select class="filter-select" id="publisher" name="publisher">
                             <option value="">Tất cả</option>
                             @foreach($publishers ?? [] as $publisher)
                                 @if($publisher->role === 'publisher')
                                     <option value="{{ $publisher->id }}" {{ request('publisher') == $publisher->id ? 'selected' : '' }}>
                                         {{ $publisher->name }}
                                     </option>
                                 @endif
                             @endforeach
                         </select>
                     </div>
                    
                    <div class="filter-group">
                        <label for="product" class="filter-label">Sản phẩm</label>
                        <select class="filter-select" id="product" name="product">
                            <option value="">Tất cả</option>
                            @foreach($products ?? [] as $product)
                                <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="campaign" class="filter-label">Campaign</label>
                        <select class="filter-select" id="campaign" name="campaign">
                            <option value="">Tất cả</option>
                            @foreach($campaigns ?? [] as $campaign)
                                <option value="{{ $campaign->id }}" {{ request('campaign') == $campaign->id ? 'selected' : '' }}>
                                    {{ $campaign->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="affiliate-btn affiliate-btn-primary">
                            <i class="fas fa-search"></i>
                            <span>Tìm kiếm</span>
                        </button>
                        <a href="{{ route('admin.affiliate-links.index') }}" class="affiliate-btn affiliate-btn-secondary">
                            <i class="fas fa-times"></i>
                            <span>Xóa bộ lọc</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="affiliate-table-card">
        <div class="table-header">
            <h3 class="table-title">Danh sách Affiliate Links</h3>
            <span class="table-count">Tổng: {{ $affiliateLinks->total() ?? 0 }} links</span>
        </div>
        
        <div class="table-body">
            @if(($affiliateLinks->count() ?? 0) > 0)
                <div class="table-wrapper">
                    <table class="affiliate-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Publisher</th>
                                <th>Sản phẩm</th>
                                <th>Campaign</th>
                                <th>URLs & Tracking</th>
                                <th>Commission</th>
                                <th>Trạng thái</th>
                                <th>Thống kê</th>
                                <th>Doanh thu</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($affiliateLinks ?? [] as $link)
                                <tr>
                                                                         <td class="table-id">{{ $link->id }}</td>
                                                                         <td class="table-publisher">
                                         <div class="user-info">
                                             <div class="user-details">
                                                 <div class="user-name">{{ $link->publisher->name ?? 'N/A' }}</div>
                                                 <div class="user-email">{{ $link->publisher->email ?? 'N/A' }}</div>
                                             </div>
                                         </div>
                                     </td>
                                     <td class="table-product">
                                         <div class="product-info">
                                             <div class="product-details">
                                                 <div class="product-name">{{ $link->product->name ?? 'N/A' }}</div>
                                                 <div class="product-category">{{ $link->product->category->name ?? 'N/A' }}</div>
                                             </div>
                                         </div>
                                     </td>
                                    <td class="table-campaign">
                                        @if($link->campaign ?? false)
                                            <span class="campaign-badge">{{ $link->campaign->name }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="table-url">
                                        @if($link->short_code)
                                            <div class="url-links">
                                                <div class="url-item">
                                                    <label class="url-label">Short URL:</label>
                                                    <a href="{{ route('tracking.short', $link->short_code) }}" target="_blank" class="url-link">
                                                        {{ route('tracking.short', $link->short_code) }}
                                                    </a>
                                                    <button class="copy-btn" onclick="copyToClipboard('{{ route('tracking.short', $link->short_code) }}')" title="Copy short URL">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>
                                                <div class="url-item">
                                                    <label class="url-label">Tracking:</label>
                                                    <code class="tracking-code">{{ $link->tracking_code }}</code>
                                                    <button class="copy-btn" onclick="copyToClipboard('{{ $link->tracking_code }}')" title="Copy tracking code">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>
                                                <div class="url-info">
                                                    <small class="text-muted">
                                                        <i class="fas fa-link"></i>
                                                        URL gốc: {{ Str::limit($link->original_url, 50) }}
                                                    </small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Chưa có short code</span>
                                        @endif
                                    </td>
                                    <td class="table-commission">
                                        <div class="commission-info">
                                            <span class="commission-badge {{ $link->product && $link->product->commission_rate != $link->commission_rate ? 'commission-different' : '' }}">
                                                {{ $link->commission_rate ?? 0 }}%
                                            </span>
                                            @if($link->product && $link->product->commission_rate != $link->commission_rate)
                                                <div class="commission-note" title="Commission rate của sản phẩm: {{ $link->product->commission_rate ?? 0 }}%">
                                                    <small class="text-muted">
                                                        <i class="fas fa-info-circle"></i>
                                                        Khác với sản phẩm
                                                    </small>
                                                </div>
                                            @elseif($link->product)
                                                <div class="commission-note">
                                                    <small class="text-muted">
                                                        <i class="fas fa-check-circle"></i>
                                                        Từ sản phẩm
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="table-status">
                                        @if(($link->status ?? '') === 'active')
                                            <span class="status-badge status-active">Đang hoạt động</span>
                                        @elseif(($link->status ?? '') === 'inactive')
                                            <span class="status-badge status-inactive">Vô hiệu hóa</span>
                                        @else
                                            <span class="status-badge status-pending">Chờ duyệt</span>
                                        @endif
                                    </td>
                                    <td class="table-stats">
                                        <div class="stats-grid">
                                            @php
                                                $totalClicks = $link->clicks->count();
                                                $totalConversions = $link->conversions->count();
                                                $conversionRate = $totalClicks > 0 ? ($totalConversions / $totalClicks) * 100 : 0;
                                            @endphp
                                            <div class="stat-item">
                                                <div class="stat-label">Clicks</div>
                                                <div class="stat-value clicks">{{ number_format($totalClicks) }}</div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-label">Conversions</div>
                                                <div class="stat-value conversions">{{ number_format($totalConversions) }}</div>
                                            </div>
                                            @if($totalClicks > 0)
                                                <div class="stat-item">
                                                    <div class="stat-label">Rate</div>
                                                    <div class="stat-value rate">{{ number_format($conversionRate, 2) }}%</div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="table-revenue">
                                        @php
                                            $totalClicks = $link->clicks->count();
                                            $totalConversions = $link->conversions->count();
                                            $clickRevenue = $totalClicks * 100; // 1 click = 100 VND
                                            $commissionRevenue = 0;
                                            if ($link->product && $totalConversions > 0) {
                                                $commissionRevenue = ($link->product->price ?? 0) * ($link->commission_rate ?? 0) / 100 * $totalConversions;
                                            }
                                            $totalRevenue = $clickRevenue + $commissionRevenue;
                                        @endphp
                                        <div class="revenue-info">
                                            <div class="revenue-total">
                                                <strong>{{ number_format($totalRevenue) }} VNĐ</strong>
                                            </div>
                                            <div class="revenue-breakdown">
                                                <small class="text-muted">
                                                    <span class="click-revenue">Click: {{ number_format($clickRevenue) }} VNĐ</span>
                                                    @if($commissionRevenue > 0)
                                                        <span class="commission-revenue"> | Commission: {{ number_format($commissionRevenue) }} VNĐ</span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="table-actions">
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.affiliate-links.edit', $link) }}" class="action-btn action-edit" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(($link->status ?? '') === 'active')
                                                <button type="button" class="action-btn action-disable toggle-status-btn" 
                                                        data-id="{{ $link->id }}" 
                                                        data-status="inactive" 
                                                        data-action="Vô hiệu hóa"
                                                        title="Vô hiệu hóa">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            @else
                                                <button type="button" class="action-btn action-enable toggle-status-btn" 
                                                        data-id="{{ $link->id }}" 
                                                        data-status="active" 
                                                        data-action="Kích hoạt"
                                                        title="Kích hoạt">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="action-btn action-delete delete-btn" 
                                                    data-id="{{ $link->id }}"
                                                    title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
                @if(request()->hasAny(['search', 'status', 'publisher', 'product', 'campaign']))
                    <!-- No search results -->
                    <div class="no-results-state">
                        <div class="no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="no-results-title">Không tìm thấy kết quả</h3>
                        <p class="no-results-description">
                            Không có affiliate link nào phù hợp với tiêu chí tìm kiếm của bạn.
                        </p>
                    </div>
                @else
                    <!-- Empty state - no items at all -->
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-link"></i>
                        </div>
                        <h3 class="empty-title">Chưa có affiliate link nào</h3>
                        <p class="empty-description">Bắt đầu tạo affiliate link đầu tiên để quản lý chiến dịch tiếp thị.</p>
                        <a href="{{ route('admin.affiliate-links.create') }}" class="affiliate-btn affiliate-btn-primary">
                            <i class="fas fa-plus"></i>
                            <span>Tạo Affiliate Link</span>
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Hidden Forms -->
<form id="toggleStatusForm" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle status buttons
    document.querySelectorAll('.toggle-status-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const status = this.getAttribute('data-status');
            const action = this.getAttribute('data-action');
            
            showConfirmPopup({
                title: 'Xác nhận',
                message: `Bạn có chắc chắn muốn ${action.toLowerCase()} affiliate link này?`,
                type: status === 'active' ? 'success' : 'warning',
                confirmText: 'Xác nhận',
                cancelText: 'Hủy bỏ',
                onConfirm: () => {
                    const form = document.getElementById('toggleStatusForm');
                    form.action = `/admin/affiliate-links/${id}/toggle-status`;
                    
                    // Clear any existing status inputs
                    const existingInputs = form.querySelectorAll('input[name="status"]');
                    existingInputs.forEach(input => input.remove());
                    
                    const statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'status';
                    statusInput.value = status;
                    form.appendChild(statusInput);
                    
                    console.log('Submitting form:', form.action, 'with status:', status);
                    form.submit();
                }
            });
        });
    });

    // Delete buttons
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            
            showConfirmPopup({
                title: 'Xác nhận xóa',
                message: 'Bạn có chắc chắn muốn xóa affiliate link này? Hành động này không thể hoàn tác.',
                type: 'danger',
                confirmText: 'Xóa',
                cancelText: 'Hủy bỏ',
                onConfirm: () => {
                    const form = document.getElementById('deleteForm');
                    form.action = `/admin/affiliate-links/${id}`;
                    form.submit();
                }
            });
        });
    });
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        const btn = event.target.closest('button');
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.add('copy-success');
        
        setTimeout(() => {
            btn.innerHTML = originalIcon;
            btn.classList.remove('copy-success');
        }, 2000);
    });
}
</script>
@endsection
