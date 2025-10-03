<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thiết lập Google 2FA - Affiliate Marketing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
    <style>
        .qr-code-container {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin: 20px 0;
        }
        .qr-code-container svg {
            max-width: 250px;
            height: auto;
            margin: 0 auto;
        }
        .secret-key {
            background: #fff;
            border: 2px dashed #e0e0e0;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            margin: 20px 0;
            word-break: break-all;
            color: #333;
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
        .steps-list {
            text-align: left;
            margin: 20px 0;
        }
        .steps-list li {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .steps-list li:last-child {
            border-bottom: none;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .status-enabled {
            background: #4caf50;
            color: white;
        }
        .status-disabled {
            background: #ff9800;
            color: white;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .action-buttons .submit-btn {
            flex: 1;
        }
        .btn-danger {
            background: #f44336;
        }
        .btn-danger:hover {
            background: #d32f2f;
        }
        .btn-secondary {
            background: #757575;
        }
        .btn-secondary:hover {
            background: #616161;
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
                            <span>Bảo mật cao</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Dễ sử dụng</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>An toàn tuyệt đối</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-form-container">
                <div class="form-header">
                    <h2>
                        <i class="fas fa-mobile-alt"></i> Google Authenticator 
                        @if($user->google2fa_enabled)
                            <span class="status-badge status-enabled">Đã bật</span>
                        @else
                            <span class="status-badge status-disabled">Chưa bật</span>
                        @endif
                    </h2>
                    <p>Tăng cường bảo mật cho tài khoản của bạn</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                @if(!$user->google2fa_enabled)
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <strong>Hướng dẫn thiết lập:</strong>
                    </div>

                    <ol class="steps-list">
                        <li><strong>Bước 1:</strong> Tải ứng dụng Google Authenticator trên điện thoại của bạn
                            <br><small style="color: #666;">
                                <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">Android</a> | 
                                <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank">iOS</a>
                            </small>
                        </li>
                        <li><strong>Bước 2:</strong> Quét mã QR bên dưới bằng ứng dụng</li>
                        <li><strong>Bước 3:</strong> Nhập mã 6 chữ số từ ứng dụng để xác nhận</li>
                    </ol>

                    <div class="qr-code-container">
                        <h3 style="margin-bottom: 15px;">Quét mã QR này:</h3>
                        {!! $qrCodeSvg !!}
                        
                        <div style="margin-top: 20px;">
                            <p style="margin-bottom: 10px;"><strong>Hoặc nhập mã thủ công:</strong></p>
                            <div class="secret-key">{{ $secret }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('2fa.enable') }}" class="auth-form">
                        @csrf
                        <div class="form-group">
                            <label for="one_time_password" class="form-label">Mã xác thực 6 chữ số</label>
                            <div class="input-wrapper">
                                <i class="fas fa-key input-icon"></i>
                                <input type="text" 
                                       id="one_time_password" 
                                       name="one_time_password" 
                                       class="form-input @error('one_time_password') error @enderror" 
                                       placeholder="Nhập mã 6 chữ số"
                                       maxlength="6"
                                       pattern="[0-9]{6}"
                                       required 
                                       autocomplete="off">
                            </div>
                            @error('one_time_password')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="submit-btn">
                                <span>Kích hoạt 2FA</span>
                                <i class="fas fa-check"></i>
                            </button>
                            <a href="{{ url()->previous() }}" class="submit-btn btn-secondary">
                                <span>Quay lại</span>
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </form>
                @else
                    <div class="info-box" style="background: #d4edda; border-left-color: #4caf50;">
                        <i class="fas fa-check-circle" style="color: #4caf50;"></i>
                        <strong>Google 2FA đã được kích hoạt!</strong>
                        <p style="margin: 10px 0 0 0;">Tài khoản của bạn đang được bảo vệ bởi xác thực 2 bước.</p>
                    </div>

                    <form method="POST" action="{{ route('2fa.disable') }}" class="auth-form" onsubmit="return confirm('Bạn có chắc muốn tắt Google 2FA? Điều này sẽ giảm bảo mật tài khoản của bạn.')">
                        @csrf
                        <div class="form-group">
                            <label for="password" class="form-label">Nhập mật khẩu để tắt 2FA</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-input @error('password') error @enderror" 
                                       placeholder="Nhập mật khẩu"
                                       required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="action-buttons">
                            <button type="submit" class="submit-btn btn-danger">
                                <span>Tắt 2FA</span>
                                <i class="fas fa-times"></i>
                            </button>
                            <a href="{{ url()->previous() }}" class="submit-btn btn-secondary">
                                <span>Quay lại</span>
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggle = input.nextElementSibling;
            const icon = toggle.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Auto-focus on OTP input
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.getElementById('one_time_password');
            if (otpInput) {
                otpInput.focus();
            }
        });
    </script>
</body>
</html>

