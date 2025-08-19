@extends('components.dashboard.layout')

@section('title', 'Quản lý Shop')

@section('content')
<div class="shop-management-content">
    <!-- Header -->
    <div class="shop-management-header">
        <div class="shop-header-left">
            <h1 class="shop-page-title">Quản lý Shop</h1>
            <p class="shop-page-description">Quản lý tất cả tài khoản shop trong hệ thống</p>
        </div>
        <div class="shop-header-right">
            <a href="{{ route('admin.users.create') }}?role=shop" class="shop-btn shop-btn-primary">
                <i class="fas fa-plus"></i>
                Thêm shop mới
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="shop-stats-grid">
        <div class="shop-stat-card">
            <div class="shop-stat-icon">
                <i class="fas fa-store"></i>
            </div>
            <div class="shop-stat-content">
                <h3>{{ $stats['total'] }}</h3>
                <p>Tổng shop</p>
            </div>
        </div>
        <div class="shop-stat-card">
            <div class="shop-stat-icon active">
                <i class="fas fa-store-alt"></i>
            </div>
            <div class="shop-stat-content">
                <h3>{{ $stats['active'] }}</h3>
                <p>Shop hoạt động</p>
            </div>
        </div>
        <div class="shop-stat-card">
            <div class="shop-stat-icon inactive">
                <i class="fas fa-store-slash"></i>
            </div>
            <div class="shop-stat-content">
                <h3>{{ $stats['inactive'] }}</h3>
                <p>Shop không hoạt động</p>
            </div>
        </div>
        <div class="shop-stat-card">
            <div class="shop-stat-icon growth">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="shop-stat-content">
                <h3>{{ $stats['this_month'] }}</h3>
                <p>Shop mới tháng này</p>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="shop-filters-card">
        <form method="GET" action="{{ route('admin.users.shop') }}" class="shop-filters-form">
            <div class="shop-filters-row">
                <div class="shop-filter-group">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                           placeholder="Tên shop hoặc email..." class="shop-filter-input">
                </div>
                <div class="shop-filter-group">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status" class="shop-filter-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                    </select>
                </div>
                <div class="shop-filter-actions">
                    <button type="submit" class="shop-btn shop-btn-primary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="{{ route('admin.users.shop') }}" class="shop-btn shop-btn-outline">
                        <i class="fas fa-times"></i>
                        Xóa bộ lọc
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Shop Users Table -->
    <div class="shop-management-card">
        <div class="shop-card-header">
            <div class="shop-card-header-left">
                <h3>Danh sách shop</h3>
                <span class="shop-total-count">{{ $shopUsers->total() }} shop</span>
            </div>
        </div>

        <div class="shop-card-body">
            @if($shopUsers->count() > 0)
                <div class="shop-table-wrapper">
                    <table class="shop-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Thông tin shop</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shopUsers as $shopUser)
                            <tr>
                                <td>{{ $shopUser->id }}</td>
                                <td>
                                    <div class="shop-info">
                                        <div class="shop-avatar">
                                            @if($shopUser->avatar)
                                                <img src="{{ asset('storage/' . $shopUser->avatar) }}" 
                                                     alt="{{ $shopUser->name }}" 
                                                     class="shop-avatar-img">
                                            @else
                                                <div class="shop-avatar-placeholder">
                                                    <i class="fas fa-store"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="shop-details">
                                            <div class="shop-name">{{ $shopUser->name }}</div>
                                            <div class="shop-email">{{ $shopUser->email }}</div>
                                            <div class="shop-role">
                                                <span class="shop-role-badge">
                                                    <i class="fas fa-store"></i> Shop
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="shop-status-badge {{ $shopUser->is_active ? 'active' : 'inactive' }}">
                                        {{ $shopUser->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="shop-date">
                                        <div class="shop-date-day">{{ $shopUser->created_at->format('d/m/Y') }}</div>
                                        <div class="shop-date-time">{{ $shopUser->created_at->format('H:i') }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="shop-action-buttons">
                                        <a href="{{ route('admin.users.show', $shopUser) }}" 
                                           class="shop-btn shop-btn-sm shop-btn-outline-info" 
                                           title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $shopUser) }}" 
                                           class="shop-btn shop-btn-sm shop-btn-outline-primary" 
                                           title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($shopUser->id !== auth()->id())
                                            <button type="button" 
                                                    class="shop-btn shop-btn-sm shop-btn-outline-warning" 
                                                    title="{{ $shopUser->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}"
                                                    onclick="showToggleShopStatusConfirm('{{ $shopUser->id }}', '{{ $shopUser->name }}', {{ $shopUser->is_active ? 'true' : 'false' }})">
                                                <i class="fas fa-{{ $shopUser->is_active ? 'ban' : 'check' }}"></i>
                                            </button>
                                            <button type="button" 
                                                    class="shop-btn shop-btn-sm shop-btn-outline-danger" 
                                                    title="Xóa"
                                                    onclick="showDeleteShopConfirm('{{ $shopUser->id }}', '{{ $shopUser->name }}')">
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
                <div class="shop-pagination-wrapper">
                    {{ $shopUsers->links() }}
                </div>
            @else
                <div class="shop-empty-state">
                    <div class="shop-empty-state-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <h3>Chưa có shop nào</h3>
                    <p>Bắt đầu tạo shop đầu tiên để quản lý hệ thống</p>
                    <a href="{{ route('admin.users.create') }}?role=shop" class="shop-btn shop-btn-primary">
                        <i class="fas fa-plus"></i>
                        Tạo shop đầu tiên
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Hidden Forms for Actions -->
<form id="toggle-shop-status-form" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<form id="delete-shop-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function showToggleShopStatusConfirm(shopId, shopName, isActive) {
    const action = isActive ? 'vô hiệu hóa' : 'kích hoạt';
    const actionText = isActive ? 'Vô hiệu hóa' : 'Kích hoạt';
    
    showConfirmPopup({
        title: `${actionText} shop`,
        message: `Bạn có chắc chắn muốn ${action} shop này?`,
        details: `Shop: ${shopName}`,
        type: 'warning',
        confirmText: actionText,
        onConfirm: () => {
            const form = document.getElementById('toggle-shop-status-form');
            form.action = `{{ route('admin.users.index') }}/${shopId}/toggle-status`;
            form.submit();
        }
    });
}

function showDeleteShopConfirm(shopId, shopName) {
    showConfirmPopup({
        title: 'Xóa shop',
        message: 'Bạn có chắc chắn muốn xóa shop này? Hành động này không thể hoàn tác.',
        details: `Shop: ${shopName}`,
        type: 'danger',
        confirmText: 'Xóa',
        onConfirm: () => {
            const form = document.getElementById('delete-shop-form');
            form.action = `{{ route('admin.users.index') }}/${shopId}`;
            form.submit();
        }
    });
}
</script>
@endsection
