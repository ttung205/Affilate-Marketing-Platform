@php
    $user = Auth::user();
    // Determine which layout to use based on user role
    if ($user->role === 'admin') {
        $layout = 'components.dashboard.layout';
        $breadcrumbRoute = 'admin.dashboard';
    } elseif ($user->role === 'publisher') {
        $layout = 'publisher.layouts.app';
        $breadcrumbRoute = 'publisher.dashboard';
    } elseif ($user->role === 'shop') {
        $layout = 'shop.layouts.app';
        $breadcrumbRoute = 'shop.dashboard';
    } else {
        $layout = 'layouts.app';
        $breadcrumbRoute = 'home';
    }
@endphp

@extends($layout)

@section('title', 'Xác thực 2 bước')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route($breadcrumbRoute) }}" class="breadcrumb-link">
        <i class="fas fa-home"></i>
        <span>Dashboard</span>
    </a>
</li>
<li class="breadcrumb-item">
    <i class="fas fa-chevron-right breadcrumb-arrow"></i>
</li>
<li class="breadcrumb-item active">
    <span>Xác thực 2 bước</span>
</li>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/settings/2fa-setup.css') }}">
@endpush

@section('content')
<div class="twofa-container">
    <div class="twofa-header">
        <div class="twofa-header-content">
            <h1><i class="fas fa-shield-alt"></i> Google Authenticator</h1>
            <p>Tăng cường bảo mật cho tài khoản của bạn</p>
        </div>
        @if($user->google2fa_enabled)
            <span class="status-badge status-enabled">
                <i class="fas fa-check-circle"></i> Đã bật
            </span>
        @else
            <span class="status-badge status-disabled">
                <i class="fas fa-exclamation-triangle"></i> Chưa bật
            </span>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="twofa-card">
        @if(!$user->google2fa_enabled)
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <strong>Thiết lập xác thực 2 bước để bảo vệ tài khoản của bạn</strong>
            </div>

            <div class="twofa-grid">
                <div class="steps-section">
                    <h3>Hướng dẫn thiết lập</h3>
                    <ul class="steps-list">
                        <li>
                            <span class="step-number">1</span>
                            <div class="step-content">
                                Tải ứng dụng Google Authenticator
                                <div class="app-links">
                                    <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">
                                        <i class="fab fa-android"></i> Android
                                    </a>
                                    <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank">
                                        <i class="fab fa-apple"></i> iOS
                                    </a>
                                </div>
                            </div>
                        </li>
                        <li>
                            <span class="step-number">2</span>
                            <div class="step-content">Quét mã QR bằng ứng dụng</div>
                        </li>
                        <li>
                            <span class="step-number">3</span>
                            <div class="step-content">Nhập mã 6 chữ số để xác nhận</div>
                        </li>
                    </ul>
                </div>

                <div class="qr-section">
                    <h3>Quét mã QR</h3>
                    <div class="qr-code-wrapper">
                        {!! $qrCodeSvg !!}
                    </div>
                    <div class="secret-key-section">
                        <p>Hoặc nhập mã thủ công:</p>
                        <div class="secret-key">{{ $secret }}</div>
                    </div>
                </div>
            </div>

            <div class="verify-section">
                <form method="POST" action="{{ route('2fa.enable') }}" id="verify-form">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-key"></i> Nhập mã xác thực 6 chữ số
                        </label>
                        <div class="otp-input-group">
                            <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="0">
                            <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="1">
                            <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="2">
                            <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="3">
                            <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="4">
                            <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="5">
                        </div>
                        <input type="hidden" name="one_time_password" id="one_time_password" value="">
                        @error('one_time_password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Kích hoạt 2FA
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="disabled-section">
                <div class="info-box success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Google 2FA đã được kích hoạt!</strong>
                    <p>Tài khoản của bạn đang được bảo vệ bởi xác thực 2 bước.</p>
                </div>

                <form method="POST" action="{{ route('2fa.disable') }}" onsubmit="return confirm('Bạn có chắc muốn tắt Google 2FA? Điều này sẽ giảm bảo mật tài khoản của bạn.')">
                    @csrf
                    <div class="form-group" style="max-width: 400px; margin: 0 auto;">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Nhập mật khẩu để tắt 2FA
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               placeholder="Nhập mật khẩu của bạn"
                               required>
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="btn-group" style="justify-content: center;">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Tắt 2FA
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const otpInputs = document.querySelectorAll('.otp-input');
        const hiddenInput = document.getElementById('one_time_password');
        const form = document.getElementById('verify-form');
        
        if (otpInputs.length === 0) return;
        
        // Auto-focus first input
        otpInputs[0].focus();
        
        // Update hidden input value
        function updateHiddenInput() {
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            hiddenInput.value = otp;
            
            // Auto-submit when all 6 digits are entered
            if (otp.length === 6 && form) {
                setTimeout(() => form.submit(), 300);
            }
        }
        
        otpInputs.forEach((input, index) => {
            // Handle input
            input.addEventListener('input', function(e) {
                // Only allow numbers
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value) {
                    // Move to next input
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                }
                
                updateHiddenInput();
            });
            
            // Handle keydown
            input.addEventListener('keydown', function(e) {
                // Backspace: move to previous input if current is empty
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
                
                // Arrow left
                if (e.key === 'ArrowLeft' && index > 0) {
                    otpInputs[index - 1].focus();
                }
                
                // Arrow right
                if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });
            
            // Handle paste
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').trim();
                const digits = pastedData.replace(/[^0-9]/g, '').slice(0, 6);
                
                if (digits.length > 0) {
                    // Fill inputs with pasted digits
                    digits.split('').forEach((digit, i) => {
                        if (otpInputs[i]) {
                            otpInputs[i].value = digit;
                        }
                    });
                    
                    // Focus on next empty input or last input
                    const nextIndex = Math.min(digits.length, otpInputs.length - 1);
                    otpInputs[nextIndex].focus();
                    
                    updateHiddenInput();
                }
            });
            
            // Select all on focus
            input.addEventListener('focus', function() {
                this.select();
            });
        });
    });
</script>
@endpush

