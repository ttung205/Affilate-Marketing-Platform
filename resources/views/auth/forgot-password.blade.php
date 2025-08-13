<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - TTung Affiliate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth/forgot-password.css') }}">
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
                        <i class="fas fa-key"></i>
                    </div>
                    <h1 class="brand-title">TTung Affiliate</h1>
                    <p class="brand-subtitle">Khôi phục tài khoản của bạn</p>
                    <div class="brand-features">
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Bảo mật tuyệt đối</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-clock"></i>
                            <span>Xử lý nhanh chóng</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-envelope"></i>
                            <span>Gửi email xác nhận</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Forgot Password Form -->
            <div class="auth-form-container">
                <div class="form-header">
                    <h2>Quên mật khẩu?</h2>
                    <p>Nhập email của bạn để nhận link đặt lại mật khẩu</p>
                </div>

                @if(session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                    @csrf

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

                    <button type="submit" class="submit-btn">
                        <span>Gửi link đặt lại mật khẩu</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>

                    <div class="auth-switch">
                        <p>Đã nhớ mật khẩu? <a href="{{ route('login') }}" class="switch-link">Đăng nhập</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>