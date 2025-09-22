@extends('publisher.layouts.app')

@section('title', 'Ví của tôi')

@section('content')
<div class="wallet-container">
    <!-- Header -->
    <div class="wallet-header">
        <div class="header-content">
            <h1 class="wallet-title">
                <i class="fas fa-wallet"></i>
                Ví của tôi
            </h1>
            <p class="wallet-subtitle">Quản lý số dư và rút tiền</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openWithdrawalModal()">
                <i class="fas fa-money-bill-wave"></i>
                Rút tiền
            </button>
        </div>
    </div>

    <!-- Sync Wallet Button -->
    <div class="sync-wallet-section">
        <button class="btn btn-outline-primary" onclick="syncWallet()" id="sync-wallet-btn">
            <i class="fas fa-sync-alt"></i>
            Đồng bộ số dư
        </button>
        <small class="text-muted">Đồng bộ số dư từ giao dịch hoa hồng</small>
    </div>

    <!-- Wallet Stats Cards -->
    <div class="wallet-stats">
        <div class="stat-card available-balance">
            <div class="stat-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-title">Số dư khả dụng</h3>
                <p class="stat-amount" id="available-balance">
                    {{ number_format($availableBalance ?? $wallet->balance, 0, ',', '.') }} VNĐ
                </p>
                <p class="stat-description">Có thể rút ngay</p>
            </div>
        </div>

        <div class="stat-card pending-balance">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-title">Số dư chờ xử lý</h3>
                <p class="stat-amount" id="pending-balance">
                    0 VNĐ
                </p>
                <p class="stat-description">Không áp dụng hold period</p>
            </div>
        </div>

        <div class="stat-card total-earned">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-title">Tổng đã kiếm</h3>
                <p class="stat-amount" id="total-earned">
                    {{ number_format($totalEarnings ?? $wallet->total_earned, 0, ',', '.') }} VNĐ
                </p>
                <p class="stat-description">Từ khi bắt đầu</p>
            </div>
        </div>

        <div class="stat-card total-withdrawn">
            <div class="stat-icon">
                <i class="fas fa-money-check"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-title">Tổng đã rút</h3>
                <p class="stat-amount" id="total-withdrawn">
                    {{ number_format($wallet->total_withdrawn, 0, ',', '.') }} VNĐ
                </p>
                <p class="stat-description">Đã rút thành công</p>
            </div>
        </div>
    </div>

    <!-- Commission Breakdown -->
    <div class="commission-breakdown">
        <h3 class="section-title">Chi tiết hoa hồng</h3>
        <div class="breakdown-grid">
            <div class="breakdown-card click-commission">
                <div class="breakdown-icon">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
                <div class="breakdown-content">
                    <h4 class="breakdown-title">Hoa hồng từ Click</h4>
                    <p class="breakdown-amount" id="click-earnings">
                        {{ number_format($clickEarnings ?? 0, 0, ',', '.') }} VNĐ
                    </p>
                    <p class="breakdown-description">Theo CPC (Cost Per Click)</p>
                </div>
            </div>

            <div class="breakdown-card conversion-commission">
                <div class="breakdown-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="breakdown-content">
                    <h4 class="breakdown-title">Hoa hồng từ Conversion</h4>
                    <p class="breakdown-amount" id="conversion-earnings">
                        {{ number_format($conversionEarnings ?? 0, 0, ',', '.') }} VNĐ
                    </p>
                    <p class="breakdown-description">Theo % hoa hồng</p>
                </div>
            </div>

            <div class="breakdown-card total-commission">
                <div class="breakdown-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="breakdown-content">
                    <h4 class="breakdown-title">Tổng hoa hồng</h4>
                    <p class="breakdown-amount" id="total-commission">
                        {{ number_format(($clickEarnings ?? 0) + ($conversionEarnings ?? 0), 0, ',', '.') }} VNĐ
                    </p>
                    <p class="breakdown-description">Click + Conversion</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3 class="section-title">Thao tác nhanh</h3>
        <div class="action-buttons">
            <a href="{{ route('publisher.withdrawal.index') }}" class="action-btn">
                <i class="fas fa-money-bill-wave"></i>
                <span>Rút tiền</span>
            </a>
            <a href="{{ route('publisher.payment-methods.index') }}" class="action-btn">
                <i class="fas fa-credit-card"></i>
                <span>Phương thức thanh toán</span>
            </a>
            <a href="{{ route('publisher.withdrawal.index') }}" class="action-btn">
                <i class="fas fa-history"></i>
                <span>Lịch sử rút tiền</span>
            </a>
            <a href="{{ route('publisher.affiliate-links.index') }}" class="action-btn">
                <i class="fas fa-link"></i>
                <span>Affiliate Links</span>
            </a>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="recent-transactions">
        <div class="section-header">
            <h3 class="section-title">Giao dịch gần đây</h3>
            <a href="{{ route('publisher.withdrawal.index') }}" class="view-all-link">Xem tất cả</a>
        </div>
        <div class="transactions-list" id="transactions-list">
            @forelse($recentTransactions as $transaction)
                <div class="transaction-item">
                    <div class="transaction-icon">
                        <i class="{{ $transaction->icon }}"></i>
                    </div>
                    <div class="transaction-content">
                        <h4 class="transaction-title">{{ $transaction->type_label }}</h4>
                        <p class="transaction-description">{{ $transaction->description }}</p>
                        <span class="transaction-time">{{ $transaction->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="transaction-amount {{ $transaction->amount > 0 ? 'positive' : 'negative' }}">
                        {{ $transaction->amount_formatted }}
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-receipt"></i>
                    <p>Chưa có giao dịch nào</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Earnings Chart -->
    <div class="earnings-chart">
        <div class="section-header">
            <h3 class="section-title">Biểu đồ thu nhập</h3>
            <div class="chart-controls">
                <select id="chart-period" class="form-select">
                    <option value="7">7 ngày qua</option>
                    <option value="30" selected>30 ngày qua</option>
                    <option value="90">90 ngày qua</option>
                    <option value="365">1 năm qua</option>
                </select>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="earningsChart"></canvas>
        </div>
    </div>
</div>

<!-- Withdrawal Modal -->
<div id="withdrawalModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rút tiền</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="withdrawalForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số tiền muốn rút (VNĐ)</label>
                                <input type="number" class="form-control" id="withdrawalAmount" 
                                       min="10000" max="{{ $wallet->balance }}" step="1000" required>
                                <div class="form-text">
                                    Số dư khả dụng: {{ number_format($wallet->balance, 0, ',', '.') }} VNĐ
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phương thức thanh toán</label>
                                <select class="form-select" id="paymentMethod" required>
                                    <option value="">Chọn phương thức</option>
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
                <button type="button" class="btn btn-primary" onclick="submitWithdrawal()">Xác nhận rút tiền</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/publisher/wallet.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/publisher/wallet-unified.js') }}"></script>
@endpush
