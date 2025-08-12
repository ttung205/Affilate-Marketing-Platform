<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoàn tất đăng ký - TTung Affiliate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
</head>

<body>
    <div class="auth-wrapper">
        <!-- Background Pattern -->
        <div class="bg-pattern"></div>

        <!-- Main Container -->
        <div class="auth-container">
            <!-- Left Side - Branding -->
            <div class="auth-branding">
                <div class="brand-content">
                    <div class="brand-logo">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h1 class="brand-title">TTung Affiliate</h1>
                    <p class="brand-subtitle">Hoàn tất đăng ký với Google</p>
                    <div class="brand-features">
                        <div class="feature-item">
                            <i class="fas fa-google"></i>
                            <span>Đã xác thực Google</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-user-check"></i>
                            <span>Thông tin đã được điền sẵn</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Bảo mật cao</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Registration Form -->
            <div class="auth-form-container">
                <div class="form-header">
                    <h2>Hoàn tất đăng ký</h2>
                    <p>Thông tin từ Google đã được điền sẵn, bạn chỉ cần chọn vai trò</p>
                </div>

                <!-- Hiển thị thông báo lỗi -->
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('google.registration.post') }}" class="auth-form">
                    @csrf

                    <div class="form-group">
                        <label for="name" class="form-label">Họ và tên</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="name" name="name" class="form-input @error('name') error @enderror"
                                placeholder="Nhập họ và tên của bạn" value="{{ $googleUserData['name'] }}" required
                                autocomplete="name">
                        </div>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email"
                                class="form-input @error('email') error @enderror" placeholder="Nhập email của bạn"
                                value="{{ $googleUserData['email'] }}" required autocomplete="email" readonly>
                        </div>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <small class="form-text">Email này sẽ được sử dụng làm tài khoản đăng nhập</small>
                    </div>

                    <div class="form-group">
                        <label for="role" class="form-label">Vai trò</label>
                        <div class="input-wrapper">
                            <i class="fas fa-briefcase input-icon"></i>
                            <select name="role" id="role" class="form-select @error('role') error @enderror" required>
                                <option value="">Chọn vai trò của bạn</option>
                                <option value="publisher">Publisher</option>
                                <option value="shop">Shop</option>
                            </select>
                        </div>
                        @error('role')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="terms-checkbox">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="terms" id="terms" required>
                            <span class="checkmark"></span>
                            <span class="checkbox-label">
                                Tôi đồng ý với <a href="#" class="terms-link">Điều khoản sử dụng</a> và <a href="#"
                                    class="terms-link">Chính sách bảo mật</a>
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="submit-btn">
                        <span>Hoàn tất đăng ký</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                    <div class="auth-switch">
                        <p>Quay lại <a href="{{ route('login') }}" class="switch-link">Đăng nhập</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Thêm CSS cho Google info box
        const style = document.createElement('style');
        style.textContent = `
            .google-info-box {
                background: linear-gradient(135deg, #4285F4 0%, #34A853 100%);
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 25px;
                color: white;
                box-shadow: 0 5px 15px rgba(66, 133, 244, 0.3);
            }
            
            .google-info-header {
                display: flex;
                align-items: center;
                margin-bottom: 15px;
                font-size: 18px;
                font-weight: 600;
            }
            
            .google-info-header i {
                margin-right: 10px;
                font-size: 20px;
            }
            
            .google-info-content {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .info-item {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .info-item i {
                width: 16px;
                opacity: 0.8;
            }
            
            .form-text {
                font-size: 12px;
                color: #666;
                margin-top: 5px;
                font-style: italic;
            }
            
            .alert {
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 8px;
                border: 1px solid transparent;
            }
            
            .alert-danger {
                color: #721c24;
                background-color: #f8d7da;
                border-color: #f5c6cb;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>
