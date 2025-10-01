@extends('shop.layouts.app')

@section('title', 'Chỉnh sửa hồ sơ - Shop Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('shop.dashboard') }}">Dashboard</a>
    </li>
    <li class="breadcrumb-item">
        <i class="fas fa-chevron-right breadcrumb-arrow"></i>
    </li>
    <li class="breadcrumb-item active">Chỉnh sửa hồ sơ</li>
@endsection

@section('content')
<div class="shop-profile-edit-container">
    <div class="shop-profile-header">
        <h1>Chỉnh sửa hồ sơ cá nhân</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="shop-profile-content">
        <div class="shop-profile-row">
            <!-- Avatar Section -->
            <div class="shop-profile-avatar-col">
                <div class="shop-profile-avatar-section">
                    <div class="shop-profile-avatar-preview">
                        @if($user->avatar)
                            @if(str_starts_with($user->avatar, 'http'))
                                <img src="{{ $user->avatar }}" alt="Avatar" id="avatarPreview" class="shop-profile-avatar-image">
                            @else
                                <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" id="avatarPreview" class="shop-profile-avatar-image">
                            @endif
                        @else
                            <div class="shop-profile-avatar-placeholder" id="avatarPreview">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="shop-profile-avatar-actions">
                        <form action="{{ route('shop.profile.update-avatar') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                            @csrf
                            @method('PUT')
                            <input type="file" name="avatar" id="avatarInput" accept="image/*" class="shop-profile-file-input">
                            <button type="button" class="shop-profile-avatar-btn shop-profile-avatar-btn-primary" onclick="document.getElementById('avatarInput').click()">
                                <i class="fas fa-camera"></i> Thay đổi ảnh
                            </button>
                        </form>
                        
                        @if($user->avatar)
                            <form action="{{ route('shop.profile.remove-avatar') }}" method="POST" class="shop-profile-avatar-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="shop-profile-avatar-btn shop-profile-avatar-btn-danger" onclick="return confirm('Bạn có chắc muốn xóa ảnh đại diện?')">
                                    <i class="fas fa-trash"></i> Xóa ảnh
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="shop-profile-form-col">
                <div class="shop-profile-form">
                    <form action="{{ route('shop.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="shop-profile-form-section">
                            <h3 class="shop-profile-section-title">Thông tin cá nhân</h3>
                            
                            <div class="shop-profile-form-row">
                                <div class="shop-profile-form-col-6">
                                    <div class="shop-profile-form-group">
                                        <label for="name" class="shop-profile-form-label">Tên hiển thị <span class="shop-profile-required">*</span></label>
                                        <input type="text" class="shop-profile-form-input @error('name') shop-profile-form-input-error @enderror" 
                                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="shop-profile-error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="shop-profile-form-col-6">
                                    <div class="shop-profile-form-group">
                                        <label for="email" class="shop-profile-form-label">Email <span class="shop-profile-required">*</span></label>
                                        <input type="email" class="shop-profile-form-input @error('email') shop-profile-form-input-error @enderror" 
                                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="shop-profile-error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="shop-profile-form-row">
                                <div class="shop-profile-form-col-6">
                                    <div class="shop-profile-form-group">
                                        <label for="phone" class="shop-profile-form-label">Số điện thoại</label>
                                        <input type="tel" class="shop-profile-form-input @error('phone') shop-profile-form-input-error @enderror" 
                                               id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                        @error('phone')
                                            <div class="shop-profile-error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="shop-profile-form-col-6">
                                    <div class="shop-profile-form-group">
                                        <label for="address" class="shop-profile-form-label">Địa chỉ</label>
                                        <textarea class="shop-profile-form-textarea @error('address') shop-profile-form-input-error @enderror" 
                                                  id="address" name="address" rows="2">{{ old('address', $user->address) }}</textarea>
                                        @error('address')
                                            <div class="shop-profile-error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="shop-profile-form-section">
                            <h3 class="shop-profile-section-title">Thay đổi mật khẩu</h3>
                            <p class="shop-profile-section-desc">Để thay đổi mật khẩu, hãy điền thông tin bên dưới. Để giữ nguyên mật khẩu, hãy để trống.</p>
                            
                            <div class="shop-profile-form-row">
                                <div class="shop-profile-form-col-6">
                                    <div class="shop-profile-form-group">
                                        <label for="current_password" class="shop-profile-form-label">Mật khẩu hiện tại</label>
                                        <input type="password" class="shop-profile-form-input @error('current_password') shop-profile-form-input-error @enderror" 
                                               id="current_password" name="current_password">
                                        @error('current_password')
                                            <div class="shop-profile-error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="shop-profile-form-col-6">
                                    <div class="shop-profile-form-group">
                                        <label for="new_password" class="shop-profile-form-label">Mật khẩu mới</label>
                                        <input type="password" class="shop-profile-form-input @error('new_password') shop-profile-form-input-error @enderror" 
                                               id="new_password" name="new_password">
                                        @error('new_password')
                                            <div class="shop-profile-error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="shop-profile-form-row">
                                <div class="shop-profile-form-col-6">
                                    <div class="shop-profile-form-group">
                                        <label for="new_password_confirmation" class="shop-profile-form-label">Xác nhận mật khẩu mới</label>
                                        <input type="password" class="shop-profile-form-input" 
                                               id="new_password_confirmation" name="new_password_confirmation">
                                    </div>
                                </div>
                                
                                <div class="shop-profile-form-col-6">
                                    <!-- Empty column for balance -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="shop-profile-form-actions">
                            <button type="submit" class="shop-profile-btn shop-profile-btn-primary">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                            <a href="{{ route('shop.dashboard') }}" class="shop-profile-btn shop-profile-btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Avatar preview functionality
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File quá lớn! Vui lòng chọn file nhỏ hơn 2MB.');
            this.value = '';
            return;
        }
        
        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Chỉ hỗ trợ file ảnh: JPG, PNG, GIF, WEBP');
            this.value = '';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            if (preview.classList.contains('shop-profile-avatar-placeholder')) {
                // Nếu là placeholder, thay thế bằng ảnh
                preview.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview" class="shop-profile-avatar-image">`;
                preview.classList.remove('shop-profile-avatar-placeholder');
            } else {
                // Nếu đã có ảnh, chỉ cập nhật src
                const img = preview.querySelector('img');
                if (img) {
                    img.src = e.target.result;
                }
            }
        };
        reader.readAsDataURL(file);
        
        // Auto-submit avatar form sau khi preview
        setTimeout(() => {
            document.getElementById('avatarForm').submit();
        }, 500);
    }
});

// Password confirmation validation
document.getElementById('new_password').addEventListener('input', function() {
    const confirmField = document.getElementById('new_password_confirmation');
    if (this.value !== confirmField.value) {
        confirmField.setCustomValidity('Mật khẩu xác nhận không khớp');
    } else {
        confirmField.setCustomValidity('');
    }
});

document.getElementById('new_password_confirmation').addEventListener('input', function() {
    const passwordField = document.getElementById('new_password');
    if (this.value !== passwordField.value) {
        this.setCustomValidity('Mật khẩu xác nhận không khớp');
    } else {
        this.setCustomValidity('');
    }
});
</script>
@endsection
