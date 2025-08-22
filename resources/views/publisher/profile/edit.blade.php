@extends('publisher.layouts.app')

@section('title', 'Chỉnh sửa hồ sơ - Publisher Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('publisher.dashboard') }}">Dashboard</a>
    </li>
    <li class="breadcrumb-item">
        <i class="fas fa-chevron-right breadcrumb-arrow"></i>
    </li>
    <li class="breadcrumb-item active">Chỉnh sửa hồ sơ</li>
@endsection

@section('content')
<div class="publisher-profile-edit-container">
    <div class="publisher-profile-header">
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

    <div class="publisher-profile-content">
        <div class="publisher-profile-row">
            <!-- Avatar Section -->
            <div class="publisher-profile-avatar-col">
                <div class="publisher-profile-avatar-section">
                    <div class="publisher-profile-avatar-preview">
                        @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" id="avatarPreview" class="publisher-profile-avatar-image">
                        @else
                            <div class="publisher-profile-avatar-placeholder" id="avatarPreview">
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="publisher-profile-avatar-actions">
                        <form action="{{ route('publisher.profile.update-avatar') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                            @csrf
                            @method('PUT')
                            <input type="file" name="avatar" id="avatarInput" accept="image/*" class="publisher-profile-file-input">
                            <button type="button" class="publisher-profile-avatar-btn publisher-profile-avatar-btn-primary" onclick="document.getElementById('avatarInput').click()">
                                <i class="fas fa-camera"></i> Thay đổi ảnh
                            </button>
                        </form>
                        
                        @if($user->avatar)
                            <form action="{{ route('publisher.profile.remove-avatar') }}" method="POST" class="publisher-profile-avatar-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="publisher-profile-avatar-btn publisher-profile-avatar-btn-danger" onclick="return confirm('Bạn có chắc muốn xóa ảnh đại diện?')">
                                    <i class="fas fa-trash"></i> Xóa ảnh
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="publisher-profile-form-col">
                <div class="publisher-profile-form">
                    <form action="{{ route('publisher.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="publisher-profile-form-section">
                            <h3 class="publisher-profile-section-title">Thông tin cá nhân</h3>
                            
                            <div class="publisher-profile-form-row">
                                <div class="publisher-profile-form-col-6">
                                    <div class="publisher-profile-form-group">
                                        <label for="name" class="publisher-profile-form-label">Họ và tên <span class="publisher-profile-required">*</span></label>
                                        <input type="text" class="publisher-profile-form-input @error('name') publisher-profile-form-input-error @enderror" 
                                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="publisher-profile-error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="publisher-profile-form-col-6">
                                    <div class="publisher-profile-form-group">
                                        <label for="email" class="publisher-profile-form-label">Email <span class="publisher-profile-required">*</span></label>
                                        <input type="email" class="publisher-profile-form-input @error('email') publisher-profile-form-input-error @enderror" 
                                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="publisher-profile-error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="publisher-profile-form-row">
                                <div class="publisher-profile-form-col-6">
                                    <div class="publisher-profile-form-group">
                                        <label for="phone" class="publisher-profile-form-label">Số điện thoại</label>
                                        <input type="tel" class="publisher-profile-form-input @error('phone') publisher-profile-form-input-error @enderror" 
                                               id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                        @error('phone')
                                            <div class="publisher-profile-error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="publisher-profile-form-col-6">
                                    <div class="publisher-profile-form-group">
                                        <label for="role" class="publisher-profile-form-label">Vai trò</label>
                                        <input type="text" class="publisher-profile-form-input publisher-profile-form-input-readonly" id="role" value="{{ ucfirst($user->role) }}" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="publisher-profile-form-row">
                                <div class="publisher-profile-form-col-6">
                                    <div class="publisher-profile-form-group">
                                        <label for="address" class="publisher-profile-form-label">Địa chỉ</label>
                                        <textarea class="publisher-profile-form-textarea @error('address') publisher-profile-form-input-error @enderror" 
                                                  id="address" name="address" rows="2">{{ old('address', $user->address) }}</textarea>
                                        @error('address')
                                            <div class="publisher-profile-error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="publisher-profile-form-col-6">
                                    <div class="publisher-profile-form-group">
                                        <label for="bio" class="publisher-profile-form-label">Giới thiệu bản thân</label>
                                        <textarea class="publisher-profile-form-textarea @error('bio') publisher-profile-form-input-error @enderror" 
                                                  id="bio" name="bio" rows="2" placeholder="Giới thiệu ngắn gọn về bản thân...">{{ old('bio', $user->bio) }}</textarea>
                                        @error('bio')
                                            <div class="publisher-profile-error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="publisher-profile-form-section">
                            <h3 class="publisher-profile-section-title">Thay đổi mật khẩu</h3>
                            <p class="publisher-profile-section-desc">Để thay đổi mật khẩu, hãy điền thông tin bên dưới. Để giữ nguyên mật khẩu, hãy để trống.</p>
                            
                            <div class="publisher-profile-form-row">
                                <div class="publisher-profile-form-col-6">
                                    <div class="publisher-profile-form-group">
                                        <label for="current_password" class="publisher-profile-form-label">Mật khẩu hiện tại</label>
                                        <input type="password" class="publisher-profile-form-input @error('current_password') publisher-profile-form-input-error @enderror" 
                                               id="current_password" name="current_password">
                                        @error('current_password')
                                            <div class="publisher-profile-error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="publisher-profile-form-col-6">
                                    <div class="publisher-profile-form-group">
                                        <label for="new_password" class="publisher-profile-form-label">Mật khẩu mới</label>
                                        <input type="password" class="publisher-profile-form-input @error('new_password') publisher-profile-form-input-error @enderror" 
                                               id="new_password" name="new_password">
                                        @error('new_password')
                                            <div class="publisher-profile-error-message">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="publisher-profile-form-row">
                                <div class="publisher-profile-form-col-6">
                                    <div class="publisher-profile-form-group">
                                        <label for="new_password_confirmation" class="publisher-profile-form-label">Xác nhận mật khẩu mới</label>
                                        <input type="password" class="publisher-profile-form-input" 
                                               id="new_password_confirmation" name="new_password_confirmation">
                                    </div>
                                </div>
                                
                                <div class="publisher-profile-form-col-6">
                                    <!-- Empty column for balance -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="publisher-profile-form-actions">
                            <button type="submit" class="publisher-profile-btn publisher-profile-btn-primary">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                            <a href="{{ route('publisher.dashboard') }}" class="publisher-profile-btn publisher-profile-btn-secondary">
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
            if (preview.classList.contains('publisher-profile-avatar-placeholder')) {
                // Nếu là placeholder, thay thế bằng ảnh
                preview.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview" class="publisher-profile-avatar-image">`;
                preview.classList.remove('publisher-profile-avatar-placeholder');
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
