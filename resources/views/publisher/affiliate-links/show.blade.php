@extends('publisher.layouts.app')

@section('title', 'Chi tiết Affiliate Link - Publisher Dashboard')

@section('content')
<div class="publisher-content">
    <!-- Header -->
    <div class="content-header">
        <div class="header-left">
            <h1>Chi tiết Affiliate Link</h1>
            <p>Thông tin chi tiết và thống kê hiệu suất</p>
        </div>
        <div class="header-right">
            <a href="{{ route('publisher.affiliate-links.edit', $affiliateLink) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <a href="{{ route('publisher.affiliate-links.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <div class="details-layout">
        <!-- Main Info -->
        <div class="main-section">
            <!-- Links Info -->
            <div class="info-card">
                <h3>Thông tin Links</h3>
                <div class="links-grid">
                    <div class="link-item">
                        <label>Short URL:</label>
                        <div class="link-group">
                            <input type="text" value="{{ route('tracking.short', $affiliateLink->short_code) }}" readonly class="form-control">
                            <button class="btn btn-sm btn-outline-primary copy-btn" onclick="copyToClipboard('{{ route('tracking.short', $affiliateLink->short_code) }}')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="link-item">
                        <label>Tracking Code:</label>
                        <div class="link-group">
                            <code>{{ $affiliateLink->tracking_code }}</code>
                            <button class="btn btn-sm btn-outline-primary copy-btn" onclick="copyToClipboard('{{ $affiliateLink->tracking_code }}')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="link-item">
                        <label>URL gốc:</label>
                        <a href="{{ $affiliateLink->original_url }}" target="_blank" class="original-link">
                            {{ $affiliateLink->original_url }} <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="info-card">
                <h3>Hoạt động gần đây</h3>
                @if($affiliateLink->clicks->count() > 0)
                    <div class="activity-list">
                        @foreach($affiliateLink->clicks->take(10) as $click)
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-mouse-pointer"></i>
                                </div>
                                <div class="activity-details">
                                    <p class="activity-text">Click từ IP: {{ $click->ip_address }}</p>
                                    <p class="activity-time">{{ $click->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Chưa có hoạt động nào</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Stats Sidebar -->
        <div class="stats-sidebar">
            <!-- Performance Stats -->
            <div class="stats-card">
                <h3>Thống kê hiệu suất</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($stats['total_clicks']) }}</div>
                        <div class="stat-label">Tổng clicks</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($stats['unique_clicks']) }}</div>
                        <div class="stat-label">Clicks duy nhất</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($stats['total_conversions']) }}</div>
                        <div class="stat-label">Conversions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ $stats['conversion_rate'] }}%</div>
                        <div class="stat-label">Tỷ lệ chuyển đổi</div>
                    </div>
                </div>
            </div>

            <!-- Revenue Stats -->
            <div class="stats-card">
                <h3>Thống kê doanh thu</h3>
                <div class="revenue-stats">
                    <div class="revenue-item">
                        <label>Commission Rate:</label>
                        @if($affiliateLink->isAutoCommissionMode())
                            <span class="commission-rate auto">
                                <i class="fas fa-sync-alt"></i> {{ $affiliateLink->effective_commission_rate }}% (Auto từ Campaign)
                            </span>
                        @else
                            <span class="commission-rate manual">
                                <i class="fas fa-edit"></i> {{ $affiliateLink->commission_rate ?? 0 }}% (Manual)
                            </span>
                        @endif
                    </div>
                    <div class="revenue-item">
                        <label>Hoa hồng từ clicks:</label>
                        <span class="revenue-value">{{ number_format($affiliateLink->click_commission) }} VNĐ</span>
                    </div>
                    <div class="revenue-item">
                        <label>Hoa hồng từ conversions:</label>
                        <span class="revenue-value">{{ number_format($affiliateLink->total_commission) }} VNĐ</span>
                    </div>
                    <div class="revenue-item">
                        <label>Tổng hoa hồng:</label>
                        <span class="revenue-value total">{{ number_format($affiliateLink->combined_commission) }} VNĐ</span>
                    </div>
                    <div class="revenue-item">
                        <label>CPC:</label>
                        <span class="cpc-value">{{ number_format($affiliateLink->cost_per_click) }} VNĐ</span>
                    </div>
                </div>
            </div>

            <!-- Link Status -->
            <div class="stats-card">
                <h3>Trạng thái</h3>
                <div class="status-info">
                    @if($affiliateLink->status === 'active')
                        <span class="status-badge status-active">
                            <i class="fas fa-check-circle"></i>
                            Đang hoạt động
                        </span>
                    @elseif($affiliateLink->status === 'inactive')
                        <span class="status-badge status-inactive">
                            <i class="fas fa-times-circle"></i>
                            Vô hiệu hóa
                        </span>
                    @else
                        <span class="status-badge status-pending">
                            <i class="fas fa-clock"></i>
                            Chờ duyệt
                        </span>
                    @endif
                    <div class="created-info">
                        <small>Tạo lúc: {{ $affiliateLink->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="stats-card">
                <h3>Thao tác nhanh</h3>
                <div class="quick-actions">
                    <a href="{{ route('tracking.short', $affiliateLink->short_code) }}" target="_blank" class="action-btn">
                        <i class="fas fa-external-link-alt"></i>
                        Test Link
                    </a>
                    @if(!$affiliateLink->clicks()->exists() && !$affiliateLink->conversions()->exists())
                        <form method="POST" action="{{ route('publisher.affiliate-links.destroy', $affiliateLink) }}" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn danger" onclick="return confirm('Bạn có chắc chắn muốn xóa link này?')">
                                <i class="fas fa-trash"></i>
                                Xóa Link
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Đã copy vào clipboard!', 'success');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('Đã copy vào clipboard!', 'success');
    });
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        if (document.body.contains(toast)) {
            document.body.removeChild(toast);
        }
    }, 3000);
}
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/publisher/affiliate-details.css') }}">
@endpush
