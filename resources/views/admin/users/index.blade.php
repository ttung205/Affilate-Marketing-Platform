@extends('components.dashboard.layout')

@section('title', 'Quản lý Người dùng')

@section('content')
<div class="user-management-content">
    <!-- Header -->
    <div class="user-management-header">
        <div class="user-header-left">
            <h1 class="user-page-title">Quản lý Người dùng</h1>
            <p class="user-page-description">Quản lý tất cả người dùng trong hệ thống</p>
        </div>
        <div class="user-header-right">
            <a href="{{ route('admin.users.create') }}" class="user-btn user-btn-primary">
                <i class="fas fa-plus"></i>
                Thêm người dùng mới
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="user-stats-grid">
        <div class="user-stat-card">
            <div class="user-stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="user-stat-content">
                <h3>{{ $stats['total'] }}</h3>
                <p>Tổng người dùng</p>
            </div>
        </div>
        <div class="user-stat-card">
            <div class="user-stat-icon active">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="user-stat-content">
                <h3>{{ $stats['active'] }}</h3>
                <p>Đang hoạt động</p>
            </div>
        </div>
        <div class="user-stat-card">
            <div class="user-stat-icon inactive">
                <i class="fas fa-user-times"></i>
            </div>
            <div class="user-stat-content">
                <h3>{{ $stats['inactive'] }}</h3>
                <p>Không hoạt động</p>
            </div>
        </div>
        <div class="user-stat-card">
            <div class="user-stat-icon admin">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="user-stat-content">
                <h3>{{ $stats['admin'] }}</h3>
                <p>Quản trị viên</p>
            </div>
        </div>
        <div class="user-stat-card">
            <div class="user-stat-icon shop">
                <i class="fas fa-store"></i>
            </div>
            <div class="user-stat-content">
                <h3>{{ $stats['shop'] }}</h3>
                <p>Shop</p>
            </div>
        </div>
        <div class="user-stat-card">
            <div class="user-stat-icon publisher">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div class="user-stat-content">
                <h3>{{ $stats['publisher'] }}</h3>
                <p>Publisher</p>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="user-filters-card">
        <form method="GET" action="{{ route('admin.users.index') }}" class="user-filters-form">
            <div class="user-filters-row">
                <div class="user-filter-group">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                           placeholder="Tên hoặc email..." class="user-filter-input">
                </div>
                <div class="user-filter-group">
                    <label for="role">Vai trò:</label>
                    <select id="role" name="role" class="user-filter-select">
                        <option value="">Tất cả vai trò</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="shop" {{ request('role') === 'shop' ? 'selected' : '' }}>Shop</option>
                        <option value="publisher" {{ request('role') === 'publisher' ? 'selected' : '' }}>Publisher</option>
                    </select>
                </div>
                <div class="user-filter-group">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status" class="user-filter-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                    </select>
                </div>
                <div class="user-filter-actions">
                    <button type="submit" class="user-btn user-btn-primary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="user-btn user-btn-outline">
                        <i class="fas fa-times"></i>
                        Xóa bộ lọc
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="user-management-card">
        <div class="user-card-header">
            <div class="user-card-header-left">
                <h3>Danh sách người dùng</h3>
                <span class="user-total-count">{{ $users->total() }} người dùng</span>
            </div>
        </div>

        <div class="user-card-body">
            @if($users->count() > 0)
                <div class="user-table-wrapper">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên người dùng</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <div class="user-name-with-avatar">
                                        <div class="user-avatar">
                                            @if($user->avatar)
                                                <img src="{{ asset('storage/' . $user->avatar) }}" 
                                                     alt="{{ $user->name }}" 
                                                     class="user-avatar-img">
                                            @else
                                                <div class="user-avatar-placeholder">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="user-name">{{ $user->name }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-email">{{ $user->email }}</div>
                                </td>
                                <td>
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
                                </td>
                                <td>
                                    <span class="user-status-badge {{ $user->is_active ? 'active' : 'inactive' }}">
                                        {{ $user->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="user-date">
                                        <div class="user-date-day">{{ $user->created_at->format('d/m/Y') }}</div>
                                        <div class="user-date-time">{{ $user->created_at->format('H:i') }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-action-buttons">
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                           class="user-btn user-btn-sm user-btn-outline-info" 
                                           title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="user-btn user-btn-sm user-btn-outline-primary" 
                                           title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <button type="button" 
                                                    class="user-btn user-btn-sm user-btn-outline-warning" 
                                                    title="{{ $user->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}"
                                                    onclick="showToggleStatusConfirm('{{ $user->id }}', '{{ $user->name }}', {{ $user->is_active ? 'true' : 'false' }})">
                                                <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                            </button>
                                            <button type="button" 
                                                    class="user-btn user-btn-sm user-btn-outline-danger" 
                                                    title="Xóa"
                                                    onclick="showDeleteConfirm('{{ $user->id }}', '{{ $user->name }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="user-pagination-wrapper">
                    {{ $users->links() }}
                </div>
            @else
                @if(request()->hasAny(['search', 'role', 'status']))
                    <!-- No search results -->
                    <div class="user-no-results-state">
                        <div class="user-no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Không tìm thấy kết quả</h3>
                        <p>Không có người dùng nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                    </div>
                @else
                    <!-- Empty state - no items at all -->
                    <div class="user-empty-state">
                        <div class="user-empty-state-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Chưa có người dùng nào</h3>
                        <p>Bắt đầu tạo người dùng đầu tiên để quản lý hệ thống</p>
                        <a href="{{ route('admin.users.create') }}" class="user-btn user-btn-primary">
                            <i class="fas fa-plus"></i>
                            Tạo người dùng đầu tiên
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Hidden Forms for Actions -->
<form id="toggle-status-form" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<form id="delete-user-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function showToggleStatusConfirm(userId, userName, isActive) {
    const action = isActive ? 'vô hiệu hóa' : 'kích hoạt';
    const actionText = isActive ? 'Vô hiệu hóa' : 'Kích hoạt';
    
    showConfirmPopup({
        title: `${actionText} người dùng`,
        message: `Bạn có chắc chắn muốn ${action} người dùng này?`,
        details: `Người dùng: ${userName}`,
        type: 'warning',
        confirmText: actionText,
        onConfirm: () => {
            const form = document.getElementById('toggle-status-form');
            form.action = `{{ route('admin.users.index') }}/${userId}/toggle-status`;
            form.submit();
        }
    });
}

function showDeleteConfirm(userId, userName) {
    showConfirmPopup({
        title: 'Xóa người dùng',
        message: 'Bạn có chắc chắn muốn xóa người dùng này? Hành động này không thể hoàn tác.',
        details: `Người dùng: ${userName}`,
        type: 'danger',
        confirmText: 'Xóa',
        onConfirm: () => {
            const form = document.getElementById('delete-user-form');
            form.action = `{{ route('admin.users.index') }}/${userId}`;
            form.submit();
        }
    });
}
</script>
@endsection
