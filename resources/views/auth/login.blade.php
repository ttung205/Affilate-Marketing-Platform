<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Affiliate Marketing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
    <!-- Chatbot CSS -->
    <link rel="stylesheet" href="{{ asset('css/chatbot/chatbot.css') }}">
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
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h1 class="brand-title">Affiliate Marketing</h1>
                    <p class="brand-subtitle">Nền tảng Affiliate Marketing hàng đầu</p>
                    <div class="brand-features">
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Kết nối Publisher & Shop</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Quản lý hiệu quả</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Bảo mật tuyệt đối</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="auth-form-container">
                <div class="form-header">
                    <h2>Chào mừng trở lại!</h2>
                    <p>Đăng nhập để tiếp tục hành trình của bạn</p>
                </div>

                <form method="POST" action="/login" class="auth-form">
                    @csrf

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email"
                                class="form-input @error('email') error @enderror" placeholder="Nhập email của bạn"
                                required autocomplete="email">
                        </div>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password"
                                class="form-input @error('password') error @enderror" placeholder="Nhập mật khẩu"
                                required autocomplete="current-password">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-options">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember', true) ? 'checked' : '' }}>
                            <span class="checkmark"></span>
                            <span class="checkbox-label">Ghi nhớ đăng nhập</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="forgot-link">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="submit-btn">
                        <span>Đăng nhập</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <div class="divider">
                        <div class="divider-line"></div>
                        <span class="divider-text">Hoặc</span>
                        <div class="divider-line"></div>
                    </div>
                    <div class="social-login">
                        <a href="{{ route('google.login') }}"
                            class="btn btn-outline-danger d-inline-flex align-items-center">
                            <svg width="20" height="20" viewBox="0 0 24 24" class="me-2">
                                <path fill="#4285F4"
                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                <path fill="#34A853"
                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                <path fill="#FBBC05"
                                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                <path fill="#EA4335"
                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                            </svg>
                            Đăng nhập bằng Google
                        </a>
                    </div>


                    <div class="auth-switch">
                        <p>Chưa có tài khoản? <a href="/register" class="switch-link">Đăng ký ngay</a></p>
                    </div>
                </form>
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
    </script>

    <!-- Chatbot Widget -->
    @include('chatbot.chatbot')

    <!-- Chatbot JS -->
    <script src="{{ asset('js/chatbot/chatbot.js') }}"></script>
</body>

</html>