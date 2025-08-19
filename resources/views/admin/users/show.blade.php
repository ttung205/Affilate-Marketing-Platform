@extends('components.dashboard.layout')

@section('title', 'Chi tiết Người dùng')

@section('content')
<div class="user-show-content">
    <!-- Header -->
    <div class="user-show-header">
        <div class="user-show-header-left">
            <h1 class="user-show-page-title">Chi tiết Người dùng</h1>
            <p class="user-show-page-description">Xem thông tin chi tiết người dùng: {{ $user->name }}</p>
        </div>
        <div class="user-show-header-right">
            <a href="{{ route('admin.users.edit', $user) }}" class="user-btn user-btn-primary">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <a href="{{ route('admin.users.index') }}" class="user-btn user-btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- User Information -->
    <div class="user-info-grid">
        <!-- Basic Information Card -->
        <div class="user-info-card">
            <div class="user-info-card-header">
                <h3><i class="fas fa-user"></i> Thông tin cơ bản</h3>
            </div>
            <div class="user-info-card-body">
                <div class="user-info-row">
                    <div class="user-info-label">ID:</div>
                    <div class="user-info-value">{{ $user->id }}</div>
                </div>
                <div class="user-info-row">
                    <div class="user-info-label">Họ và tên:</div>
                    <div class="user-info-value">{{ $user->name }}</div>
                </div>
                <div class="user-info-row">
                    <div class="user-info-label">Email:</div>
                    <div class="user-info-value">{{ $user->email }}</div>
                </div>
                <div class="user-info-row">
                    <div class="user-info-label">Vai trò:</div>
                    <div class="user-info-value">
                        <span class="user-role-badge user-role-{{ $user->role }}">
                            @switch($user->role)
                                @case('admin')
                                    <i class="fas fa-user-shield"></i> Admin
                                    @break
                                @case('shop')
                                    <i class="fas fa-store"></i> Shop
                                    @break
                                @case('publisher')
                                    <i class="fas fa-bullhorn"></i> Publisher
                                    @break
                                @default
                                    <i class="fas fa-user"></i> User
                            @endswitch
                        </span>
                    </div>
                </div>
                <div class="user-info-row">
                    <div class="user-info-label">Trạng thái:</div>
                    <div class="user-info-value">
                        <span class="user-status-badge {{ $user->is_active ? 'active' : 'inactive' }}">
                            {{ $user->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information Card -->
        <div class="user-info-card">
            <div class="user-info-card-header">
                <h3><i class="fas fa-cog"></i> Thông tin tài khoản</h3>
            </div>
            <div class="user-info-card-body">
                <div class="user-info-row">
                    <div class="user-info-label">Ngày tạo:</div>
                    <div class="user-info-value">{{ $user->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
                <div class="user-info-row">
                    <div class="user-info-label">Cập nhật lần cuối:</div>
                    <div class="user-info-value">{{ $user->updated_at->format('d/m/Y H:i:s') }}</div>
                </div>
                <div class="user-info-row">
                    <div class="user-info-label">Xác thực email:</div>
                    <div class="user-info-value">
                        @if($user->email_verified_at)
                            <span class="user-verified-badge">
                                <i class="fas fa-check-circle"></i> Đã xác thực
                            </span>
                        @else
                            <span class="user-unverified-badge">
                                <i class="fas fa-times-circle"></i> Chưa xác thực
                            </span>
                        @endif
                    </div>
                </div>
                <div class="user-info-row">
                    <div class="user-info-label">Google ID:</div>
                    <div class="user-info-value">{{ $user->google_id ?: 'Không có' }}</div>
                </div>
            </div>
        </div>

        <!-- Avatar Card -->
        <div class="user-info-card">
            <div class="user-info-card-header">
                <h3><i class="fas fa-image"></i> Hình ảnh</h3>
            </div>
            <div class="user-info-card-body">
                <div class="user-avatar-display">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" 
                             alt="{{ $user->name }}" 
                             class="user-avatar-large">
                    @else
                        <div class="user-avatar-placeholder-large">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                </div>
                <div class="user-avatar-actions">
                    <button class="user-btn user-btn-sm user-btn-outline-primary">
                        <i class="fas fa-upload"></i> Thay đổi ảnh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="user-actions-card">
        <div class="user-actions-card-header">
            <h3><i class="fas fa-tools"></i> Thao tác nhanh</h3>
        </div>
        <div class="user-actions-card-body">
            <div class="user-quick-actions">
                @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.toggle-status', $user) }}" 
                          method="POST" 
                          class="d-inline user-toggle-form">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="user-btn user-btn-warning">
                            <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                            {{ $user->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }} tài khoản
                        </button>
                    </form>

                    <form action="{{ route('admin.users.destroy', $user) }}" 
                          method="POST" 
                          class="d-inline user-delete-form"
                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này? Hành động này không thể hoàn tác!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="user-btn user-btn-danger">
                            <i class="fas fa-trash"></i>
                            Xóa người dùng
                        </button>
                    </form>
                @else
                    <div class="user-action-disabled">
                        <i class="fas fa-info-circle"></i>
                        Bạn không thể thực hiện các thao tác này trên chính mình
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="user-stats-card">
        <div class="user-stats-card-header">
            <h3><i class="fas fa-chart-bar"></i> Thống kê người dùng</h3>
        </div>
        <div class="user-stats-card-body">
            <div class="user-stats-grid">
                <div class="user-stat-item">
                    <div class="user-stat-number">{{ $user->created_at->diffForHumans() }}</div>
                    <div class="user-stat-label">Thời gian tham gia</div>
                </div>
                <div class="user-stat-item">
                    <div class="user-stat-number">{{ $user->updated_at->diffForHumans() }}</div>
                    <div class="user-stat-label">Cập nhật lần cuối</div>
                </div>
                <div class="user-stat-item">
                    <div class="user-stat-number">
                        @if($user->email_verified_at)
                            {{ $user->email_verified_at->diffForHumans() }}
                        @else
                            Chưa xác thực
                        @endif
                    </div>
                    <div class="user-stat-label">Xác thực email</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
