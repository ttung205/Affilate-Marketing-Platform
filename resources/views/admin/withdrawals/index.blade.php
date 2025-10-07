@extends('components.dashboard.layout')

@section('title', 'Quản lý rút tiền')

@section('content')
<div class="withdrawals-container">
    <!-- Header -->
    <div class="withdrawals-header">
        <div class="header-content">
            <h1 class="withdrawals-title">
                Quản lý rút tiền
            </h1>
            <p class="withdrawals-subtitle">Phê duyệt và quản lý yêu cầu rút tiền từ publishers</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-outline-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt"></i>
                Làm mới
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="withdrawals-stats">
        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-title">Chờ duyệt</h3>
                <p class="stat-amount" id="pending-count">{{ $stats['pending_count'] ?? 0 }}</p>
                <p class="stat-description">Yêu cầu mới</p>
            </div>
        </div>

        <div class="stat-card approved">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-title">Đã duyệt</h3>
                <p class="stat-amount" id="approved-count">{{ $stats['approved_count'] ?? 0 }}</p>
                <p class="stat-description">Đang xử lý</p>
            </div>
        </div>

        <div class="stat-card completed">
            <div class="stat-icon">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-title">Hoàn thành</h3>
                <p class="stat-amount" id="completed-count">{{ $stats['completed_count'] ?? 0 }}</p>
                <p class="stat-description">Thành công</p>
            </div>
        </div>

        <div class="stat-card rejected">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-title">Từ chối</h3>
                <p class="stat-amount" id="rejected-count">{{ $stats['rejected_count'] ?? 0 }}</p>
                <p class="stat-description">Không duyệt</p>
            </div>
        </div>

        <div class="stat-card total-amount">
            <div class="stat-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-title">Tổng tiền</h3>
                <p class="stat-amount" id="total-amount">{{ number_format($stats['total_amount'] ?? 0, 0, ',', '.') }} VNĐ</p>
                <p class="stat-description">Tháng này</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="withdrawals-filters">
        <div class="filter-group">
            <label class="filter-label">Trạng thái:</label>
            <select id="status-filter" class="form-select">
                <option value="">Tất cả</option>
                <option value="pending">Chờ duyệt</option>
                <option value="approved">Đã duyệt</option>
                <option value="processing">Đang xử lý</option>
                <option value="completed">Hoàn thành</option>
                <option value="rejected">Từ chối</option>
                <option value="cancelled">Đã hủy</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Publisher:</label>
            <input type="text" id="publisher-filter" class="form-control" placeholder="Tìm theo tên publisher">
        </div>

        <div class="filter-group">
            <label class="filter-label">Từ ngày:</label>
            <input type="date" id="date-from" class="form-control">
        </div>

        <div class="filter-group">
            <label class="filter-label">Đến ngày:</label>
            <input type="date" id="date-to" class="form-control">
        </div>

        <div class="filter-group">
            <label class="filter-label">Số tiền từ:</label>
            <input type="number" id="amount-min" class="form-control" placeholder="0" step="1000">
        </div>

        <div class="filter-group">
            <label class="filter-label">Số tiền đến:</label>
            <input type="number" id="amount-max" class="form-control" placeholder="10000000" step="1000">
        </div>

        <div class="filter-group">
            <button class="btn btn-primary" onclick="applyFilters()">
                <i class="fas fa-filter"></i>
                Lọc
            </button>
            <button class="btn btn-outline-secondary" onclick="clearFilters()">
                <i class="fas fa-times"></i>
                Xóa bộ lọc
            </button>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bulk-actions" id="bulk-actions" style="display: none;">
        <div class="bulk-info">
            <span id="selected-count">0</span> yêu cầu được chọn
        </div>
        <div class="bulk-buttons">
            <button class="btn btn-success" onclick="bulkApprove()">
                <i class="fas fa-check"></i>
                Duyệt
            </button>
            <button class="btn btn-danger" onclick="bulkReject()">
                <i class="fas fa-times"></i>
                Từ chối
            </button>
        </div>
    </div>

    <!-- Withdrawals List -->
    <div class="withdrawals-list">
        <div class="list-header">
            <h3 class="section-title">Danh sách yêu cầu rút tiền</h3>
            <div class="list-actions">
                <button class="btn btn-outline-primary" onclick="refreshList()">
                    <i class="fas fa-sync-alt"></i>
                    Làm mới
                </button>
            </div>
        </div>

        <div class="withdrawals-table-container">
            <table class="withdrawals-table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="select-all" onchange="toggleSelectAll()">
                        </th>
                        <th>Mã yêu cầu</th>
                        <th>Publisher</th>
                        <th>Số tiền</th>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="withdrawals-tbody">
                    @forelse($withdrawals as $withdrawal)
                        <tr data-withdrawal-id="{{ $withdrawal->id }}">
                            <td>
                                <input type="checkbox" class="withdrawal-checkbox" value="{{ $withdrawal->id }}">
                            </td>
                            <td>
                                <span class="withdrawal-id">#{{ $withdrawal->id }}</span>
                            </td>
                            <td>
                                <div class="publisher-info">
                                    <div class="publisher-name">{{ $withdrawal->publisher->name ?? 'N/A' }}</div>
                                    <div class="publisher-email">{{ $withdrawal->publisher->email ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="withdrawal-amount">
                                    {{ number_format($withdrawal->amount, 0, ',', '.') }} VNĐ
                                </span>
                                @if($withdrawal->fee > 0)
                                    <br>
                                    <small class="text-muted">
                                        Phí: {{ number_format($withdrawal->fee, 0, ',', '.') }} VNĐ
                                    </small>
                                @endif
                            </td>
                            <td>
                                <div class="payment-method">
                                    <i class="{{ $withdrawal->paymentMethod->icon ?? 'fas fa-credit-card' }}"></i>
                                    <span>{{ $withdrawal->paymentMethod->type_label ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $withdrawal->status }}">
                                    {{ $withdrawal->status_label }}
                                </span>
                            </td>
                            <td>
                                <span class="withdrawal-date">
                                    {{ $withdrawal->created_at->format('d/m/Y H:i') }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="viewWithdrawal({{ $withdrawal->id }})"
                                            title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($withdrawal->status === 'pending')
                                        <button class="btn btn-sm btn-success" 
                                                onclick="approveWithdrawal({{ $withdrawal->id }})"
                                                title="Duyệt">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="rejectWithdrawal({{ $withdrawal->id }})"
                                                title="Từ chối">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @elseif($withdrawal->status === 'approved')
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="completeWithdrawal({{ $withdrawal->id }})"
                                                title="Hoàn thành">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-money-bill-wave"></i>
                                <p>Chưa có yêu cầu rút tiền nào</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            {{ $withdrawals->links() }}
        </div>
    </div>
</div>

<!-- Withdrawal Detail Modal -->
<div id="withdrawalDetailModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết yêu cầu rút tiền</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="withdrawalDetailContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Duyệt yêu cầu rút tiền</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="approveForm">
                    <input type="hidden" id="approveWithdrawalId">
                    <div class="mb-3">
                        <label class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control" id="approveNotes" rows="3" 
                                  placeholder="Thêm ghi chú cho publisher..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" onclick="confirmApprove()">Duyệt</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Từ chối yêu cầu rút tiền</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    <input type="hidden" id="rejectWithdrawalId">
                    <div class="mb-3">
                        <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectReason" rows="3" 
                                  placeholder="Nhập lý do từ chối..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú bổ sung (tùy chọn)</label>
                        <textarea class="form-control" id="rejectNotes" rows="2" 
                                  placeholder="Thêm ghi chú..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">Từ chối</button>
            </div>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div id="completeModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hoàn thành yêu cầu rút tiền</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- QR Code Section -->
                <div id="qrCodeSection" style="display: none;" class="mb-4">
                    <!-- QR code will be injected here by JavaScript -->
                </div>
                
                <!-- Always visible notice -->
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle"></i>
                    <strong>Lưu ý:</strong> Sau khi chuyển tiền thành công, vui lòng nhập mã giao dịch bên dưới và nhấn "Hoàn thành".
                </div>
                
                <!-- Transaction Reference Form -->
                <form id="completeForm">
                    <input type="hidden" id="completeWithdrawalId">
                    <div class="mb-3">
                        <label class="form-label">Mã giao dịch <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="transactionReference" 
                               placeholder="Nhập mã giao dịch từ ngân hàng sau khi chuyển tiền..." required>
                        <small class="text-muted">Mã giao dịch có thể tìm thấy trong lịch sử giao dịch ngân hàng</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control" id="completeNotes" rows="3" 
                                  placeholder="Thêm ghi chú..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="confirmComplete()">
                    <i class="fas fa-check-double"></i> Xác nhận hoàn thành
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/withdrawals.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/admin/withdrawals.js') }}"></script>
<script>
// Real-time updates
window.Echo.private('admin.withdrawals')
    .listen('.withdrawal.status.updated', (e) => {
        console.log('Withdrawal status updated:', e);
        
        // Update the specific row in table
        updateWithdrawalRow(e.withdrawal);
        
        // Update stats
        refreshStats();
        
        // Show notification
        showRealtimeNotification(e);
    });

function updateWithdrawalRow(withdrawal) {
    const row = document.querySelector(`tr[data-withdrawal-id="${withdrawal.id}"]`);
    if (row) {
        // Update status badge
        const statusBadge = row.querySelector('.status-badge');
        if (statusBadge) {
            statusBadge.className = `status-badge status-${withdrawal.status}`;
            statusBadge.textContent = getStatusLabel(withdrawal.status);
        }
        
        // Update action buttons
        const actionButtons = row.querySelector('.action-buttons');
        if (actionButtons) {
            actionButtons.innerHTML = generateActionButtons(withdrawal);
        }
        
        // Add highlight effect
        row.classList.add('updated-row');
        setTimeout(() => {
            row.classList.remove('updated-row');
        }, 3000);
    }
}

function refreshStats() {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.loadStats();
    }
}



function getStatusLabel(status) {
    const labels = {
        'pending_otp': 'Chờ OTP',
        'pending': 'Chờ duyệt',
        'approved': 'Đã duyệt',
        'processing': 'Đang xử lý',
        'completed': 'Hoàn thành',
        'rejected': 'Từ chối',
        'cancelled': 'Đã hủy'
    };
    return labels[status] || status;
}

function generateActionButtons(withdrawal) {
    let buttons = `
        <button class="btn btn-sm btn-outline-primary" 
                onclick="adminWithdrawalsManager.viewWithdrawal(${withdrawal.id})"
                title="Xem chi tiết">
            <i class="fas fa-eye"></i>
        </button>
    `;
    
    if (withdrawal.status === 'pending') {
        buttons += `
            <button class="btn btn-sm btn-success" 
                    onclick="adminWithdrawalsManager.approveWithdrawal(${withdrawal.id})"
                    title="Duyệt">
                <i class="fas fa-check"></i>
            </button>
            <button class="btn btn-sm btn-danger" 
                    onclick="adminWithdrawalsManager.rejectWithdrawal(${withdrawal.id})"
                    title="Từ chối">
                <i class="fas fa-times"></i>
            </button>
        `;
    } else if (withdrawal.status === 'approved') {
        buttons += `
            <button class="btn btn-sm btn-primary" 
                    onclick="adminWithdrawalsManager.completeWithdrawal(${withdrawal.id})"
                    title="Hoàn thành">
                <i class="fas fa-check-double"></i>
            </button>
        `;
    }
    
    return buttons;
}
</script>

<style>
.updated-row {
    background-color: #fef3cd !important;
    animation: highlight 3s ease-out;
}

@keyframes highlight {
    0% { background-color: #fef3cd; }
    100% { background-color: transparent; }
}
</style>
@endpush
