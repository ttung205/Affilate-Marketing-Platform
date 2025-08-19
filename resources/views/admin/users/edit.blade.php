@extends('components.dashboard.layout')

@section('title', 'Chỉnh sửa Người dùng')

@section('content')
<div class="user-edit-content">
    <!-- Header -->
    <div class="user-edit-header">
        <div class="user-edit-header-left">
            <h1 class="user-edit-page-title">Chỉnh sửa Người dùng</h1>
            <p class="user-edit-page-description">Cập nhật thông tin người dùng: {{ $user->name }}</p>
        </div>
        <div class="user-edit-header-right">
            <a href="{{ route('admin.users.index') }}" class="user-btn user-btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="user-edit-card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="user-edit-form">
            @csrf
            @method('PUT')
            
            <div class="user-form-section">
                <h3 class="user-form-section-title">Thông tin cơ bản</h3>
                
                <div class="user-form-row">
                    <div class="user-form-group">
                        <label for="name" class="user-form-label">Họ và tên <span class="required">*</span></label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $user->name) }}" 
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
                               value="{{ old('email', $user->email) }}" 
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
                        <label for="password" class="user-form-label">Mật khẩu mới</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="user-form-input @error('password') is-invalid @enderror" 
                               placeholder="Để trống nếu không thay đổi">
                        <small class="user-form-help">Để trống nếu không muốn thay đổi mật khẩu</small>
                        @error('password')
                            <div class="user-form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="user-form-group">
                        <label for="password_confirmation" class="user-form-label">Xác nhận mật khẩu mới</label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               class="user-form-input" 
                               placeholder="Nhập lại mật khẩu mới">
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
                            <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                            <option value="shop" {{ old('role', $user->role) === 'shop' ? 'selected' : '' }}>Shop</option>
                            <option value="publisher" {{ old('role', $user->role) === 'publisher' ? 'selected' : '' }}>Publisher</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
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
                                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <span class="user-form-checkbox-text">Kích hoạt tài khoản</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="user-form-section">
                <h3 class="user-form-section-title">Thông tin bổ sung</h3>
                
                <div class="user-form-row">
                    <div class="user-form-group">
                        <label class="user-form-label">ID người dùng</label>
                        <input type="text" 
                               value="{{ $user->id }}" 
                               class="user-form-input" 
                               readonly>
                    </div>
                    
                    <div class="user-form-group">
                        <label class="user-form-label">Ngày tạo</label>
                        <input type="text" 
                               value="{{ $user->created_at->format('d/m/Y H:i:s') }}" 
                               class="user-form-input" 
                               readonly>
                    </div>
                </div>

                <div class="user-form-row">
                    <div class="user-form-group">
                        <label class="user-form-label">Cập nhật lần cuối</label>
                        <input type="text" 
                               value="{{ $user->updated_at->format('d/m/Y H:i:s') }}" 
                               class="user-form-input" 
                               readonly>
                    </div>
                    
                    <div class="user-form-group">
                        <label class="user-form-label">Xác thực email</label>
                        <input type="text" 
                               value="{{ $user->email_verified_at ? 'Đã xác thực' : 'Chưa xác thực' }}" 
                               class="user-form-input" 
                               readonly>
                    </div>
                </div>
            </div>

            <div class="user-form-actions">
                <button type="submit" class="user-btn user-btn-primary">
                    <i class="fas fa-save"></i>
                    Cập nhật người dùng
                </button>
                <a href="{{ route('admin.users.index') }}" class="user-btn user-btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
