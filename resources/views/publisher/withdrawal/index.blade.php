@extends('publisher.layouts.app')

@section('title', 'Rút tiền')

@section('content')
<div class="withdrawal-container">
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <!-- Header -->
    <div class="withdrawal-header">
        <div class="header-content">
            <h1 class="withdrawal-title">
                Rút tiền
            </h1>
            <p class="withdrawal-subtitle">Quản lý yêu cầu rút tiền của bạn</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openWithdrawalModal()">
                <i class="fas fa-plus"></i>
                Tạo yêu cầu rút tiền
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="withdrawal-stats">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-title">Số dư khả dụng</h3>
                <p class="stat-amount" id="available-balance">
                    {{ number_format($wallet->balance ?? 0, 0, ',', '.') }} VNĐ
                </p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-title">Đang chờ duyệt</h3>
                <p class="stat-amount" id="pending-count">
                    {{ $withdrawals->where('status', 'pending')->count() }}
                </p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-title">Đã hoàn thành</h3>
                <p class="stat-amount" id="completed-count">
                    {{ $withdrawals->where('status', 'completed')->count() }}
                </p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-title">Bị từ chối</h3>
                <p class="stat-amount" id="rejected-count">
                    {{ $withdrawals->where('status', 'rejected')->count() }}
                </p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="withdrawal-filters">
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
            <label class="filter-label">Từ ngày:</label>
            <input type="date" id="date-from" class="form-control">
        </div>

        <div class="filter-group">
            <label class="filter-label">Đến ngày:</label>
            <input type="date" id="date-to" class="form-control">
        </div>

        <div class="filter-group">
            <button class="btn btn-outline-primary" onclick="applyFilters()">
                <i class="fas fa-filter"></i>
                Lọc
            </button>
            <button class="btn btn-outline-secondary" onclick="clearFilters()">
                <i class="fas fa-times"></i>
                Xóa bộ lọc
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
                        <th>Mã yêu cầu</th>
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
                                <span class="withdrawal-id">#{{ $withdrawal->id }}</span>
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
                                    <i class="{{ $withdrawal->paymentMethod->icon }}"></i>
                                    <span>{{ $withdrawal->paymentMethod->type_label }}</span>
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
                                    @if($withdrawal->canBeCancelled())
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="cancelWithdrawal({{ $withdrawal->id }})"
                                                title="Hủy yêu cầu">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
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

<!-- Withdrawal Modal -->
<div id="withdrawalModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tạo yêu cầu rút tiền</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="withdrawalForm" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số tiền muốn rút (VNĐ)</label>
                                <input type="number" class="form-control" id="withdrawalAmount" name="amount"
                                       min="10000" step="1000" required>
                                <div class="form-text">
                                    Số dư khả dụng: <span id="availableBalanceText">
                                        {{ number_format($wallet->balance ?? 0, 0, ',', '.') }} VNĐ
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tài khoản thanh toán</label>
                                <select class="form-select" id="paymentMethod" name="payment_method_id" required>
                                    <option value="">Chọn tài khoản</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method['id'] }}" 
                                                data-type="{{ $method['type'] }}"
                                                data-fee-rate="{{ $method['fee_rate'] }}">
                                            {{ $method['display_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="withdrawal-summary">
                        <div class="summary-row">
                            <span>Số tiền rút:</span>
                            <span id="summaryAmount">0 VNĐ</span>
                        </div>
                        <div class="summary-row">
                            <span>Phí giao dịch:</span>
                            <span id="summaryFee">0 VNĐ</span>
                        </div>
                        <div class="summary-row total">
                            <span>Số tiền thực nhận:</span>
                            <span id="summaryNetAmount">0 VNĐ</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="submitWithdrawal()">Tạo yêu cầu</button>
            </div>
        </div>
    </div>
</div>

<!-- Withdrawal Detail Modal -->
<div id="withdrawalDetailModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
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

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/publisher/withdrawal.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/publisher/withdrawal.js') }}"></script>
@endpush
