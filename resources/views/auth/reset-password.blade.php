<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - TTung Affiliate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth/reset-password.css') }}">
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
                        <i class="fas fa-lock"></i>
                    </div>
                    <h1 class="brand-title">TTung Affiliate</h1>
                    <p class="brand-subtitle">Tạo mật khẩu mới</p>
                    <div class="brand-features">
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Mật khẩu mạnh</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Bảo mật cao</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-user-check"></i>
                            <span>Xác thực an toàn</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Reset Password Form -->
            <div class="auth-form-container">
                <div class="form-header">
                    <h2>Đặt lại mật khẩu</h2>
                    <p>Tạo mật khẩu mới cho tài khoản của bạn</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-error">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="auth-form">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="email" name="email"
                                class="form-input @error('email') error @enderror" 
                                placeholder="Nhập email của bạn"
                                value="{{ old('email') }}" required autocomplete="email">
                        </div>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Mật khẩu mới</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password"
                                class="form-input @error('password') error @enderror" 
                                placeholder="Nhập mật khẩu mới" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="form-input" 
                                placeholder="Nhập lại mật khẩu mới" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <span>Đặt lại mật khẩu</span>
                        <i class="fas fa-check"></i>
                    </button>

                    <div class="auth-switch">
                        <p>Đã nhớ mật khẩu? <a href="{{ route('login') }}" class="switch-link">Đăng nhập</a></p>
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
</body>

</html>