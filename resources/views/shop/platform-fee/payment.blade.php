@extends('shop.layouts.app')

@section('title', 'Thanh toán phí sàn - QR Code')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/shop/platform-fee.css') }}">
@endpush

@section('content')
<div class="qr-payment-content">
    <div class="qr-payment-container">
        <a href="{{ route('shop.platform-fee.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>

        <div class="qr-payment-card">
            <div class="qr-header">
                <i class="fas fa-qrcode"></i>
                <h2>Quét mã QR để thanh toán</h2>
            </div>

            <div class="qr-body">
                <div class="qr-left">
                    <div class="qr-code-wrapper">
                        <img src="{{ $qrCode }}" alt="QR Code Payment">
                    </div>
                </div>

                <div class="qr-right">
                    <div class="qr-info">
                        <div class="info-row">
                            <span>Số tiền:</span>
                            <strong class="amount">{{ number_format($totalDebt, 0, ',', '.') }} VND</strong>
                        </div>
                        <div class="info-row">
                            <span>Ngân hàng:</span>
                            <strong>MBBank</strong>
                        </div>
                        <div class="info-row">
                            <span>Số tài khoản:</span>
                            <strong>0375401903</strong>
                        </div>
                        <div class="info-row">
                            <span>Tên tài khoản:</span>
                            <strong>DO THANH TUNG</strong>
                        </div>
                        <div class="info-row">
                            <span>Nội dung:</span>
                            <strong>PHI SAN SHOP {{ auth()->user()->id }}</strong>
                        </div>
                    </div>

                    <div class="qr-actions">
                        <form id="confirmPaymentForm" action="{{ route('shop.platform-fee.confirm') }}" method="POST">
                            @csrf
                            <button type="button" class="btn btn-primary" onclick="confirmPayment()">
                                <i class="fas fa-check"></i> Đã thanh toán
                            </button>
                        </form>
                        <a href="{{ route('shop.platform-fee.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmPayment() {
    showConfirmPopup({
        title: 'Xác nhận thanh toán',
        message: 'Bạn đã chuyển khoản thành công?',
        type: 'success',
        confirmText: 'Đã chuyển khoản',
        cancelText: 'Chưa',
        onConfirm: function() {
            document.getElementById('confirmPaymentForm').submit();
        }
    });
}
</script>
@endpush

