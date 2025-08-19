@extends('components.dashboard.layout')

@section('title', 'Chi tiết Campaign')

@section('content')
<div class="campaigns-container">
    <!-- Header Section -->
    <div class="campaigns-header">
        <div class="campaigns-header-left">
            <h1 class="campaigns-title">Chi tiết Campaign</h1>
            <nav class="campaigns-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Admin</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Quản lý Affiliate</span>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('admin.campaigns.index') }}">Campaigns</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">{{ $campaign->name }}</span>
            </nav>
        </div>
        <div class="campaigns-header-right">
            <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="campaigns-btn campaigns-btn-primary">
                <i class="fas fa-edit"></i>
                <span>Chỉnh sửa</span>
            </a>
            <a href="{{ route('admin.campaigns.index') }}" class="campaigns-btn campaigns-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Quay lại</span>
            </a>
        </div>
    </div>

    <!-- Campaign Info Grid -->
    <div class="campaigns-info-grid">
        <!-- Basic Info Card -->
        <div class="campaigns-info-card">
            <div class="info-card-header">
                <h3>Thông tin cơ bản</h3>
            </div>
            <div class="info-card-body">
                <div class="info-row">
                    <span class="info-label">ID:</span>
                    <span class="info-value">{{ $campaign->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tên:</span>
                    <span class="info-value">{{ $campaign->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Trạng thái:</span>
                    <span class="status-badge status-{{ $campaign->status }}">
                        @switch($campaign->status)
                            @case('active')
                                Đang hoạt động
                                @break
                            @case('paused')
                                Tạm dừng
                                @break
                            @case('completed')
                                Hoàn thành
                                @break
                            @default
                                Nháp
                        @endswitch
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày tạo:</span>
                    <span class="info-value">{{ $campaign->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cập nhật lần cuối:</span>
                    <span class="info-value">{{ $campaign->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Campaign Details Card -->
        <div class="campaigns-info-card">
            <div class="info-card-header">
                <h3>Chi tiết Campaign</h3>
            </div>
            <div class="info-card-body">
                <div class="info-row">
                    <span class="info-label">Mô tả:</span>
                    <span class="info-value">{{ $campaign->description ?: 'Không có mô tả' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày bắt đầu:</span>
                    <span class="info-value">{{ $campaign->start_date ? $campaign->start_date->format('d/m/Y') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày kết thúc:</span>
                    <span class="info-value">{{ $campaign->end_date ? $campaign->end_date->format('d/m/Y') : 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngân sách:</span>
                    <span class="info-value">{{ number_format($campaign->budget) }} VNĐ</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Mục tiêu conversions:</span>
                    <span class="info-value">{{ number_format($campaign->target_conversions) }}</span>
                </div>
            </div>
        </div>

        <!-- Performance Stats Card -->
        <div class="campaigns-info-card">
            <div class="info-card-header">
                <h3>Thống kê hiệu suất</h3>
            </div>
            <div class="info-card-body">
                <div class="info-row">
                    <span class="info-label">Tổng clicks:</span>
                    <span class="info-value">{{ number_format($campaign->total_clicks) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tổng conversions:</span>
                    <span class="info-value">{{ number_format($campaign->total_conversions) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tỷ lệ chuyển đổi:</span>
                    <span class="info-value">{{ number_format($campaign->conversion_rate, 2) }}%</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tổng hoa hồng:</span>
                    <span class="info-value">{{ number_format($campaign->total_commission) }} VNĐ</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Affiliate Links Section -->
    <div class="campaigns-affiliate-links-card">
        <div class="affiliate-links-header">
            <h3>Affiliate Links trong Campaign</h3>
            <span class="links-count">{{ $campaign->affiliateLinks->count() }} links</span>
        </div>
        
        <div class="affiliate-links-body">
            @if($campaign->affiliateLinks->count() > 0)
                <div class="affiliate-links-table-wrapper">
                    <table class="affiliate-links-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tracking Code</th>
                                <th>Publisher</th>
                                <th>Sản phẩm</th>
                                <th>Commission</th>
                                <th>Trạng thái</th>
                                <th>Thống kê</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaign->affiliateLinks as $link)
                                <tr>
                                    <td>{{ $link->id }}</td>
                                    <td>
                                        <code class="tracking-code">{{ $link->tracking_code }}</code>
                                    </td>
                                    <td>{{ $link->publisher->name ?? 'N/A' }}</td>
                                    <td>{{ $link->product->name ?? 'N/A' }}</td>
                                    <td>{{ $link->commission_rate }}%</td>
                                    <td>
                                        <span class="status-badge status-{{ $link->status }}">
                                            @switch($link->status)
                                                @case('active')
                                                    Đang hoạt động
                                                    @break
                                                @case('inactive')
                                                    Vô hiệu hóa
                                                    @break
                                                @default
                                                    Chờ duyệt
                                            @endswitch
                                        </span>
                                    </td>
                                    <td>
                                        <div class="stats-mini">
                                            <span class="stat-mini clicks">{{ number_format($link->total_clicks) }} clicks</span>
                                            <span class="stat-mini conversions">{{ number_format($link->total_conversions) }} conv</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.affiliate-links.show', $link) }}" class="action-btn action-view" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.affiliate-links.edit', $link) }}" class="action-btn action-edit" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <h3>Chưa có affiliate link nào</h3>
                    <p>Campaign này chưa có affiliate link nào được tạo.</p>
                    <a href="{{ route('admin.affiliate-links.create') }}" class="campaigns-btn campaigns-btn-primary">
                        <i class="fas fa-plus"></i>
                        <span>Tạo Affiliate Link</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="campaigns-actions-card">
        <div class="actions-card-header">
            <h3>Thao tác nhanh</h3>
        </div>
        <div class="actions-card-body">
            <div class="quick-actions">
                @if($campaign->status === 'active')
                    <button type="button" class="campaigns-btn campaigns-btn-warning toggle-status-btn" 
                            data-id="{{ $campaign->id }}" 
                            data-status="paused" 
                            data-action="Tạm dừng">
                        <i class="fas fa-pause"></i>
                        <span>Tạm dừng Campaign</span>
                    </button>
                @elseif($campaign->status === 'paused')
                    <button type="button" class="campaigns-btn campaigns-btn-success toggle-status-btn" 
                            data-id="{{ $campaign->id }}" 
                            data-status="active" 
                            data-action="Kích hoạt">
                        <i class="fas fa-play"></i>
                        <span>Kích hoạt Campaign</span>
                    </button>
                @endif
                
                <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="campaigns-btn campaigns-btn-primary">
                    <i class="fas fa-edit"></i>
                    <span>Chỉnh sửa</span>
                </a>
                
                <button type="button" class="campaigns-btn campaigns-btn-danger delete-btn" 
                        data-id="{{ $campaign->id }}">
                    <i class="fas fa-trash"></i>
                    <span>Xóa Campaign</span>
                </button>
            </div>
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

    // Delete button
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
