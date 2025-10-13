@extends('components.dashboard.layout')

@section('title', 'Quản lý Thanh toán Phí sàn')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/platform-fee-payments.css') }}">
@endpush

@section('content')
<div class="platform-fee-payments-content">
    <div class="platform-fee-payments-header">
        <div class="header-left">
            <h1 class="page-title">Quản lý Thanh toán Phí sàn</h1>
        </div>
        <div class="header-right">
            <div class="stats-summary">
                <div class="stat-item pending">
                    <i class="fas fa-clock"></i>
                    <div class="stat-info">
                        <span class="stat-value">{{ $pendingCount }}</span>
                        <span class="stat-label">Chờ duyệt</span>
                    </div>
                </div>
                <div class="stat-item approved">
                    <i class="fas fa-check-circle"></i>
                    <div class="stat-info">
                        <span class="stat-value">{{ $paidCount }}</span>
                        <span class="stat-label">Đã duyệt</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <button class="filter-tab {{ $status === 'pending' ? 'active' : '' }}" 
                onclick="window.location.href='{{ route('admin.platform-fee-payments.index', ['status' => 'pending']) }}'">
            <i class="fas fa-clock"></i>
            Chờ duyệt ({{ $pendingCount }})
        </button>
        <button class="filter-tab {{ $status === 'paid' ? 'active' : '' }}" 
                onclick="window.location.href='{{ route('admin.platform-fee-payments.index', ['status' => 'paid']) }}'">
            <i class="fas fa-check-circle"></i>
            Đã duyệt ({{ $paidCount }})
        </button>
        <button class="filter-tab {{ $status === 'rejected' ? 'active' : '' }}" 
                onclick="window.location.href='{{ route('admin.platform-fee-payments.index', ['status' => 'rejected']) }}'">
            <i class="fas fa-times-circle"></i>
            Từ chối ({{ $rejectedCount }})
        </button>
        <button class="filter-tab {{ $status === 'all' ? 'active' : '' }}" 
                onclick="window.location.href='{{ route('admin.platform-fee-payments.index') }}'">
            <i class="fas fa-list"></i>
            Tất cả
        </button>
    </div>

    <!-- Payments List -->
    <div class="payments-list">
        @forelse($payments as $payment)
        <div class="payment-card {{ $payment->status }}">
            <div class="payment-header">
                <div class="shop-info">
                    <div class="shop-avatar">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="shop-details">
                        <h3 class="shop-name">{{ $payment->shop->name }}</h3>
                        <p class="shop-email">{{ $payment->shop->email }}</p>
                    </div>
                </div>
                <div class="payment-info-grid">
                    <div class="info-item">
                        <span class="info-label">Phí sàn ({{ $payment->fee_percentage }}%):</span>
                        <span class="info-value highlight">{{ number_format($payment->fee_amount, 0, ',', '.') }} VNĐ</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Thời gian:</span>
                        <span class="info-value">{{ $payment->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
                <div class="payment-status">
                    <span class="status-badge {{ $payment->status }}">
                        @if($payment->status === 'pending')
                            <i class="fas fa-clock"></i> CHỜ DUYỆT
                        @elseif($payment->status === 'paid')
                            <i class="fas fa-check-circle"></i> ĐÃ DUYỆT
                        @elseif($payment->status === 'rejected')
                            <i class="fas fa-times-circle"></i> TỪ CHỐI
                        @endif
                    </span>
                </div>
                @if($payment->status === 'pending')
                <div class="payment-actions-header">
                    <button type="button" class="payment-btn-approve" onclick="approvePayment({{ $payment->id }})">
                        <i class="fas fa-check"></i>
                        Duyệt
                    </button>
                    <button type="button" class="payment-btn-reject" onclick="rejectPayment({{ $payment->id }})">
                        <i class="fas fa-times"></i>
                        Từ chối
                    </button>
                </div>
                @endif
            </div>

            @if($payment->note || $payment->admin_note)
            <div class="payment-body">
                <div class="notes-section">
                    @if($payment->note)
                    <div class="payment-note">
                        <i class="fas fa-sticky-note"></i>
                        <span>{{ $payment->note }}</span>
                    </div>
                    @endif

                    @if($payment->admin_note)
                    <div class="admin-note">
                        <i class="fas fa-user-shield"></i>
                        <span><strong>Admin:</strong> {{ $payment->admin_note }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Không có thanh toán nào</h3>
            <p>
                @if($status === 'pending')
                    Chưa có thanh toán nào chờ duyệt
                @elseif($status === 'paid')
                    Chưa có thanh toán nào được duyệt
                @elseif($status === 'rejected')
                    Chưa có thanh toán nào bị từ chối
                @else
                    Chưa có thanh toán nào
                @endif
            </p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($payments->hasPages())
    <div class="pagination-wrapper">
        {{ $payments->links() }}
    </div>
    @endif
</div>

<!-- QR Code Modal -->
<div id="qrModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>QR Code thanh toán</h3>
            <button class="modal-close" onclick="closeQRModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="qr-display">
                <img id="qrImage" src="" alt="QR Code">
            </div>
            <div class="qr-info">
                <p class="qr-amount">Số tiền: <span id="qrAmount"></span> VNĐ</p>
            </div>
        </div>
    </div>
</div>

<!-- Approve Form (Hidden) -->
<form id="approveForm" method="POST" style="display: none;">
    @csrf
</form>

<!-- Reject Modal -->
<div id="rejectModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Từ chối thanh toán</h3>
            <button class="modal-close" onclick="closeRejectModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="admin_note">Lý do từ chối <span class="required">*</span></label>
                    <textarea id="admin_note" 
                              name="admin_note" 
                              class="form-control" 
                              rows="4" 
                              required
                              placeholder="Nhập lý do từ chối thanh toán..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeRejectModal()">
                    Hủy
                </button>
                <button type="submit" class="btn-danger">
                    <i class="fas fa-times"></i>
                    Xác nhận từ chối
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function viewQRCode(paymentId, qrUrl, amount) {
    document.getElementById('qrImage').src = qrUrl;
    document.getElementById('qrAmount').textContent = new Intl.NumberFormat('vi-VN').format(amount);
    document.getElementById('qrModal').classList.add('show');
}

function closeQRModal() {
    document.getElementById('qrModal').classList.remove('show');
}

function approvePayment(paymentId) {
    showConfirmPopup({
        title: 'Duyệt thanh toán',
        message: 'Bạn xác nhận shop đã thanh toán phí sàn thành công?',
        type: 'success',
        confirmText: 'Xác nhận duyệt',
        cancelText: 'Hủy',
        onConfirm: function() {
            const form = document.getElementById('approveForm');
            form.action = `/admin/platform-fee-payments/${paymentId}/verify`;
            form.submit();
        }
    });
}

function rejectPayment(paymentId) {
    const form = document.getElementById('rejectForm');
    form.action = `/admin/platform-fee-payments/${paymentId}/reject`;
    document.getElementById('rejectModal').classList.add('show');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('show');
    document.getElementById('admin_note').value = '';
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        closeQRModal();
        closeRejectModal();
    }
});
</script>
@endpush

