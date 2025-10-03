<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực 2FA - Affiliate Marketing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
    <style>
        .verification-icon {
            text-align: center;
            margin: 30px 0;
        }
        .verification-icon i {
            font-size: 80px;
            color: #2196f3;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        .otp-input-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin: 20px 0;
        }
        .otp-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s;
            background: #fff;
        }
        .otp-input:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            transform: scale(1.05);
        }
        .otp-input:not(:placeholder-shown) {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .info-box i {
            color: #2196f3;
            margin-right: 10px;
        }
        .help-text {
            text-align: center;
            color: #666;
            margin: 20px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="bg-pattern"></div>

        <div class="auth-container">
            <div class="auth-branding">
                <div class="brand-content">
                    <div class="brand-logo">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h1 class="brand-title">Xác thực 2 bước</h1>
                    <p class="brand-subtitle">Bảo vệ tài khoản của bạn</p>
                    <div class="brand-features">
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>An toàn</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Nhanh chóng</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Bảo mật</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-form-container">
                <div class="verification-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>

                <div class="form-header">
                    <h2>Xác thực 2 bước</h2>
                    <p>Nhập mã 6 chữ số từ Google Authenticator</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <i class="fas fa-exclamation-circle"></i> 
                        @foreach($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </div>
                @endif

                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    Mở ứng dụng <strong>Google Authenticator</strong> trên điện thoại của bạn và nhập mã 6 chữ số.
                </div>

                <form method="POST" action="{{ route('2fa.verify.post') }}" class="auth-form" id="verifyForm">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Mã xác thực</label>
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
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="submit-btn">
                        <span>Xác thực</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <div class="help-text">
                        <p>Không nhận được mã?</p>
                        <p style="margin-top: 10px;">
                            <a href="{{ route('login') }}" style="color: #2196f3; text-decoration: none;">
                                <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const otpInputs = document.querySelectorAll('.otp-input');
            const hiddenInput = document.getElementById('one_time_password');
            const form = document.getElementById('verifyForm');
            
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
</body>
</html>

