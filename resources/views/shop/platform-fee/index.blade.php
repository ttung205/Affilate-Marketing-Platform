@extends('shop.layouts.app')

@section('title', 'Thanh toán phí sàn')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/shop/platform-fee.css') }}">
@endpush

@section('content')
<div class="platform-fee-payment-content">
    <div class="platform-fee-payment-header">
        <div class="platform-fee-header-left">
            <h1 class="platform-fee-page-title">Thanh toán phí sàn</h1>
            <p class="platform-fee-page-description">Quản lý và thanh toán phí sàn cho sản phẩm của bạn</p>
        </div>
    </div>

    @if($currentFee)
    <!-- Summary Cards -->
    <div class="fee-summary-grid">
        <div class="fee-summary-card">
            <div class="fee-summary-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="fee-summary-content">
                <div class="fee-summary-label">Phí sàn hiện tại</div>
                <div class="fee-summary-value">{{ $currentFee->fee_percentage }}%</div>
            </div>
        </div>

        <div class="fee-summary-card">
            <div class="fee-summary-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fas fa-box"></i>
            </div>
            <div class="fee-summary-content">
                <div class="fee-summary-label">Tổng giá trị sản phẩm</div>
                <div class="fee-summary-value">{{ number_format($totalProductsValue, 0, ',', '.') }} VND</div>
            </div>
        </div>

        <div class="fee-summary-card">
            <div class="fee-summary-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="fee-summary-content">
                <div class="fee-summary-label">Tổng phí sàn</div>
                <div class="fee-summary-value">{{ number_format($feeAmount, 0, ',', '.') }} VND</div>
            </div>
        </div>

        <div class="fee-summary-card {{ $totalDebt > 0 ? 'debt' : 'paid' }}">
            <div class="fee-summary-icon" style="background: {{ $totalDebt > 0 ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #10b981, #059669)' }};">
                <i class="fas {{ $totalDebt > 0 ? 'fa-exclamation-circle' : 'fa-check-circle' }}"></i>
            </div>
            <div class="fee-summary-content">
                <div class="fee-summary-label">{{ $totalDebt > 0 ? 'Tổng nợ' : 'Trạng thái' }}</div>
                <div class="fee-summary-value">
                    @if($totalDebt > 0)
                        {{ number_format($totalDebt, 0, ',', '.') }} VND
                    @else
                        Đã thanh toán đủ
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Action -->
    @if($totalDebt > 0)
    <div class="fee-payment-action-card">
        <div class="payment-action-content">
            <div class="payment-action-icon">
                <i class="fas fa-credit-card"></i>
            </div>
            <div class="payment-action-info">
                <h3>Bạn có khoản phí sàn cần thanh toán</h3>
                <p>Số tiền cần thanh toán: <strong>{{ number_format($totalDebt, 0, ',', '.') }} VND</strong></p>
            </div>
            <form action="{{ route('shop.platform-fee.generate-qr') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-payment">
                    <i class="fas fa-qrcode"></i>
                    Thanh Toán
                </button>
            </form>
        </div>
    </div>
    @else
    <div class="fee-payment-success-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3>Bạn đã thanh toán đủ phí sàn!</h3>
        <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi</p>
    </div>
    @endif

    <!-- Payment History -->
    <div class="fee-payment-history-card">
        <div class="fee-card-header">
            <h3><i class="fas fa-history"></i> Lịch sử thanh toán</h3>
        </div>
        <div class="fee-card-body">
            @if($payments->count() > 0)
                <div class="fee-table-wrapper">
                    <table class="fee-payment-table">
                        <thead>
                            <tr>
                                <th>Mã GD</th>
                                <th>Số tiền</th>
                                <th>Phí (%)</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Ngày thanh toán</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                            <tr>
                                <td class="payment-id">#{{ $payment->id }}</td>
                                <td class="payment-amount">{{ number_format($payment->fee_amount, 0, ',', '.') }} VND</td>
                                <td>{{ $payment->fee_percentage }}%</td>
                                <td>
                                    <span class="payment-status-badge {{ $payment->status }}">
                                        @if($payment->status === 'paid')
                                            <i class="fas fa-check-circle"></i> Đã thanh toán
                                        @elseif($payment->status === 'pending')
                                            <i class="fas fa-clock"></i> Chờ xử lý
                                        @else
                                            <i class="fas fa-times-circle"></i> Đã hủy
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : 'Chưa thanh toán' }}</td>
                                <td>{{ $payment->note ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="fee-pagination">
                    {{ $payments->links() }}
                </div>
            @else
                <div class="fee-empty">
                    <i class="fas fa-inbox"></i>
                    <p>Chưa có lịch sử thanh toán nào</p>
                </div>
            @endif
        </div>
    </div>

    @else
    <!-- No Fee Setting -->
    <div class="fee-no-setting-card">
        <div class="no-setting-icon">
            <i class="fas fa-info-circle"></i>
        </div>
        <h3>Chưa có cài đặt phí sàn</h3>
        <p>Hiện tại chưa có cài đặt phí sàn nào được áp dụng. Vui lòng liên hệ với admin để biết thêm chi tiết.</p>
    </div>
    @endif
</div>
@endsection

