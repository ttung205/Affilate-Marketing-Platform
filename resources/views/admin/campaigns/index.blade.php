@extends('components.dashboard.layout')

@section('title', 'Quản lý Campaigns')

@section('content')
<div class="campaigns-container">
    <!-- Header Section -->
    <div class="campaigns-header">
        <div class="campaigns-header-left">
            <h1 class="campaigns-title">Quản lý Campaigns</h1>
        </div>
        <div class="campaigns-header-right">
            <a href="{{ route('admin.campaigns.create') }}" class="campaigns-btn campaigns-btn-primary">
                <i class="fas fa-plus"></i>
                <span>Tạo Campaign</span>
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="campaigns-stats-grid">
        <div class="campaigns-stat-card campaigns-stat-primary">
            <div class="campaigns-stat-icon">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div class="campaigns-stat-content">
                <h3>{{ $stats['total'] ?? 0 }}</h3>
                <p>Tổng Campaigns</p>
            </div>
        </div>

        <div class="campaigns-stat-card campaigns-stat-success">
            <div class="campaigns-stat-icon">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="campaigns-stat-content">
                <h3>{{ $stats['active'] ?? 0 }}</h3>
                <p>Đang hoạt động</p>
            </div>
        </div>

        <div class="campaigns-stat-card campaigns-stat-warning">
            <div class="campaigns-stat-icon">
                <i class="fas fa-pause-circle"></i>
            </div>
            <div class="campaigns-stat-content">
                <h3>{{ $stats['paused'] ?? 0 }}</h3>
                <p>Tạm dừng</p>
            </div>
        </div>

        <div class="campaigns-stat-card campaigns-stat-info">
            <div class="campaigns-stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="campaigns-stat-content">
                <h3>{{ number_format($stats['total_budget'] ?? 0) }}</h3>
                <p>Tổng ngân sách</p>
            </div>
        </div>

        <div class="campaigns-stat-card campaigns-stat-secondary">
            <div class="campaigns-stat-icon">
                <i class="fas fa-mouse-pointer"></i>
            </div>
            <div class="campaigns-stat-content">
                <h3>{{ number_format($stats['total_clicks'] ?? 0) }}</h3>
                <p>Tổng Clicks</p>
            </div>
        </div>

        <div class="campaigns-stat-card campaigns-stat-danger">
            <div class="campaigns-stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="campaigns-stat-content">
                <h3>{{ number_format($stats['total_conversions'] ?? 0) }}</h3>
                <p>Tổng Conversions</p>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="campaigns-filters-card">
        <div class="filters-header">
            <h3 class="filters-title">Bộ lọc & Tìm kiếm</h3>
        </div>
        <div class="filters-body">
            <form method="GET" action="{{ route('admin.campaigns.index') }}" class="filters-form">
                <div class="filters-row">
                    <div class="filter-group">
                        <label for="search" class="filter-label">Tìm kiếm</label>
                        <input type="text" class="filter-input" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Tên campaign, mô tả...">
                    </div>
                    
                    <div class="filter-group">
                        <label for="status" class="filter-label">Trạng thái</label>
                        <select class="filter-select" id="status" name="status">
                            <option value="">Tất cả</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                            <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Tạm dừng</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_range" class="filter-label">Khoảng thời gian</label>
                        <select class="filter-select" id="date_range" name="date_range">
                            <option value="">Tất cả</option>
                            <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>Tuần này</option>
                            <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>Tháng này</option>
                            <option value="this_quarter" {{ request('date_range') == 'this_quarter' ? 'selected' : '' }}>Quý này</option>
                            <option value="this_year" {{ request('date_range') == 'this_year' ? 'selected' : '' }}>Năm nay</option>
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="campaigns-btn campaigns-btn-primary">
                            <i class="fas fa-search"></i>
                            <span>Tìm kiếm</span>
                        </button>
                        <a href="{{ route('admin.campaigns.index') }}" class="campaigns-btn campaigns-btn-secondary">
                            <i class="fas fa-times"></i>
                            <span>Xóa bộ lọc</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="campaigns-table-card">
        <div class="table-header">
            <h3 class="table-title">Danh sách Campaigns</h3>
            <span class="table-count">Tổng: {{ $campaigns->total() ?? 0 }} campaigns</span>
        </div>
        
        <div class="table-body">
            @if(($campaigns->count() ?? 0) > 0)
                <div class="table-wrapper">
                    <table class="campaigns-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên Campaign</th>
                                <th>Mô tả</th>
                                <th>Thời gian</th>
                                <th>Ngân sách</th>
                                <th>Mục tiêu</th>
                                <th>Trạng thái</th>
                                <th>Thống kê</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaigns ?? [] as $campaign)
                                <tr>
                                    <td class="table-id">{{ $campaign->id }}</td>
                                    <td class="table-name">
                                        <div class="campaign-name">{{ $campaign->name }}</div>
                                    </td>
                                    <td class="table-description">
                                        <div class="campaign-description">
                                            {{ Str::limit($campaign->description, 60) ?: 'Không có mô tả' }}
                                        </div>
                                    </td>
                                    <td class="table-dates">
                                        <div class="campaign-dates">
                                            <div class="start-date">
                                                <span class="date-label">Bắt đầu:</span>
                                                <span class="date-value">{{ $campaign->start_date ? $campaign->start_date->format('d/m/Y') : 'N/A' }}</span>
                                            </div>
                                            <div class="end-date">
                                                <span class="date-label">Kết thúc:</span>
                                                <span class="date-value">{{ $campaign->end_date ? $campaign->end_date->format('d/m/Y') : 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="table-budget">
                                        <span class="budget-amount">{{ number_format($campaign->budget ?? 0) }} VNĐ</span>
                                    </td>
                                    <td class="table-target">
                                        <span class="target-conversions">{{ number_format($campaign->target_conversions ?? 0) }} conversions</span>
                                    </td>
                                    <td class="table-status">
                                        @if(($campaign->status ?? '') === 'active')
                                            <span class="status-badge status-active">Đang hoạt động</span>
                                        @elseif(($campaign->status ?? '') === 'paused')
                                            <span class="status-badge status-paused">Tạm dừng</span>
                                        @elseif(($campaign->status ?? '') === 'completed')
                                            <span class="status-badge status-completed">Hoàn thành</span>
                                        @else
                                            <span class="status-badge status-draft">Nháp</span>
                                        @endif
                                    </td>
                                    <td class="table-stats">
                                        <div class="stats-grid">
                                            <div class="stat-item">
                                                <div class="stat-label">Clicks</div>
                                                <div class="stat-value clicks">{{ number_format($campaign->total_clicks ?? 0) }}</div>
                                            </div>
                                            <div class="stat-item">
                                                <div class="stat-label">Conversions</div>
                                                <div class="stat-value conversions">{{ number_format($campaign->total_conversions ?? 0) }}</div>
                                            </div>
                                            @if(($campaign->total_clicks ?? 0) > 0)
                                                <div class="stat-item">
                                                    <div class="stat-label">Rate</div>
                                                    <div class="stat-value rate">{{ number_format($campaign->conversion_rate ?? 0, 2) }}%</div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="table-date">{{ ($campaign->created_at ?? now())->format('d/m/Y H:i') }}</td>
                                    <td class="table-actions">
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.campaigns.show', $campaign) }}" class="action-btn action-view" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="action-btn action-edit" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(($campaign->status ?? '') === 'active')
                                                <button type="button" class="action-btn action-pause toggle-status-btn" 
                                                        data-id="{{ $campaign->id }}" 
                                                        data-status="paused" 
                                                        data-action="Tạm dừng"
                                                        title="Tạm dừng">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            @elseif(($campaign->status ?? '') === 'paused')
                                                <button type="button" class="action-btn action-play toggle-status-btn" 
                                                        data-id="{{ $campaign->id }}" 
                                                        data-status="active" 
                                                        data-action="Kích hoạt"
                                                        title="Kích hoạt">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="action-btn action-delete delete-btn" 
                                                    data-id="{{ $campaign->id }}"
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
                    {{ $campaigns->appends(request()->query())->links() }}
                </div>
            @else
                @if(request()->hasAny(['search', 'status', 'date_range']))
                    <!-- No search results -->
                    <div class="no-results-state">
                        <div class="no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="no-results-title">Không tìm thấy kết quả</h3>
                        <p class="no-results-description">
                            Không có campaign nào phù hợp với tiêu chí tìm kiếm của bạn.
                        </p>
                        <div class="no-results-actions">
                            <a href="{{ route('admin.campaigns.index') }}" class="campaigns-btn campaigns-btn-secondary">
                                <i class="fas fa-times"></i>
                                <span>Xóa bộ lọc</span>
                            </a>
                            <a href="{{ route('admin.campaigns.create') }}" class="campaigns-btn campaigns-btn-primary">
                                <i class="fas fa-plus"></i>
                                <span>Tạo Campaign mới</span>
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Empty state - no items at all -->
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3 class="empty-title">Chưa có campaign nào</h3>
                        <p class="empty-description">Bắt đầu tạo campaign đầu tiên để quản lý chiến dịch tiếp thị.</p>
                        <a href="{{ route('admin.campaigns.create') }}" class="campaigns-btn campaigns-btn-primary">
                            <i class="fas fa-plus"></i>
                            <span>Tạo Campaign</span>
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
                message: `Bạn có chắc chắn muốn ${action.toLowerCase()} campaign này?`,
                type: status === 'active' ? 'success' : 'warning',
                confirmText: 'Xác nhận',
                cancelText: 'Hủy bỏ',
                onConfirm: () => {
                    const form = document.getElementById('toggleStatusForm');
                    form.action = `/admin/campaigns/${id}/toggle-status`;
                    
                    // Clear any existing status inputs
                    const existingInputs = form.querySelectorAll('input[name="status"]');
                    existingInputs.forEach(input => input.remove());
                    
                    const statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'status';
                    statusInput.value = status;
                    form.appendChild(statusInput);
                    
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
                message: 'Bạn có chắc chắn muốn xóa campaign này? Hành động này không thể hoàn tác.',
                type: 'danger',
                confirmText: 'Xóa',
                cancelText: 'Hủy bỏ',
                onConfirm: () => {
                    const form = document.getElementById('deleteForm');
                    form.action = `/admin/campaigns/${id}`;
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
