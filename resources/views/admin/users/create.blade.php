@extends('components.dashboard.layout')

@section('title', 'Thêm Người dùng mới')

@section('content')
<div class="user-create-content">
    <!-- Header -->
    <div class="user-create-header">
        <div class="user-create-header-left">
            <h1 class="user-create-page-title">Thêm Người dùng mới</h1>
            <p class="user-create-page-description">Tạo tài khoản người dùng mới trong hệ thống</p>
        </div>
        <div class="user-create-header-right">
            <a href="{{ route('admin.users.index') }}" class="user-btn user-btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Create Form -->
    <div class="user-create-card">
        <form method="POST" action="{{ route('admin.users.store') }}" class="user-create-form">
            @csrf
            
            <div class="user-form-section">
                <h3 class="user-form-section-title">Thông tin cơ bản</h3>
                
                <div class="user-form-row">
                    <div class="user-form-group">
                        <label for="name" class="user-form-label">Họ và tên <span class="required">*</span></label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               class="user-form-input @error('name') is-invalid @enderror" 
                               placeholder="Nhập họ và tên"
                               required>
                        @error('name')
                            <div class="user-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="user-form-group">
                        <label for="email" class="user-form-label">Email <span class="required">*</span></label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               class="user-form-input @error('email') is-invalid @enderror" 
                               placeholder="Nhập email"
                               required>
                        @error('email')
                            <div class="user-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="user-form-row">
                    <div class="user-form-group">
                        <label for="password" class="user-form-label">Mật khẩu <span class="required">*</span></label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="user-form-input @error('password') is-invalid @enderror" 
                               placeholder="Nhập mật khẩu"
                               required>
                        @error('password')
                            <div class="user-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="user-form-group">
                        <label for="password_confirmation" class="user-form-label">Xác nhận mật khẩu <span class="required">*</span></label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               class="user-form-input" 
                               placeholder="Nhập lại mật khẩu"
                               required>
                    </div>
                </div>
            </div>

            <div class="user-form-section">
                <h3 class="user-form-section-title">Cài đặt tài khoản</h3>
                
                <div class="user-form-row">
                    <div class="user-form-group">
                        <label for="role" class="user-form-label">Vai trò <span class="required">*</span></label>
                        <select id="role" 
                                name="role" 
                                class="user-form-select @error('role') is-invalid @enderror"
                                required>
                            <option value="">Chọn vai trò</option>
                            <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                            <option value="shop" {{ old('role') === 'shop' ? 'selected' : '' }}>Shop</option>
                            <option value="publisher" {{ old('role') === 'publisher' ? 'selected' : '' }}>Publisher</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')
                            <div class="user-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="user-form-group">
                        <label class="user-form-label">Trạng thái</label>
                        <div class="user-form-checkbox-group">
                            <label class="user-form-checkbox">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <span class="user-form-checkbox-text">Kích hoạt tài khoản</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="user-form-actions">
                <button type="submit" class="user-btn user-btn-primary">
                    <i class="fas fa-save"></i>
                    Tạo người dùng
                </button>
                <a href="{{ route('admin.users.index') }}" class="user-btn user-btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-select role from URL parameter
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const roleParam = urlParams.get('role');
    if (roleParam) {
        document.getElementById('role').value = roleParam;
    }
});
</script>
@endsection
