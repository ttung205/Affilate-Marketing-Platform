@extends('components.dashboard.layout')

@section('title', 'Quản lý Publisher')

@section('content')
<div class="publisher-management-content">
    <!-- Header -->
    <div class="publisher-management-header">
        <div class="publisher-header-left">
            <h1 class="publisher-page-title">Quản lý Publisher</h1>
            <p class="publisher-page-description">Quản lý tất cả tài khoản publisher trong hệ thống</p>
        </div>
        <div class="publisher-header-right">
            <a href="{{ route('admin.users.create') }}?role=publisher" class="publisher-btn publisher-btn-primary">
                <i class="fas fa-plus"></i>
                Thêm publisher mới
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="publisher-stats-grid">
        <div class="publisher-stat-card">
            <div class="publisher-stat-icon">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div class="publisher-stat-content">
                <h3>{{ $stats['total'] }}</h3>
                <p>Tổng publisher</p>
            </div>
        </div>
        <div class="publisher-stat-card">
            <div class="publisher-stat-icon active">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div class="publisher-stat-content">
                <h3>{{ $stats['active'] }}</h3>
                <p>Publisher hoạt động</p>
            </div>
        </div>
        <div class="publisher-stat-card">
            <div class="publisher-stat-icon inactive">
                <i class="fas fa-volume-mute"></i>
            </div>
            <div class="publisher-stat-content">
                <h3>{{ $stats['inactive'] }}</h3>
                <p>Publisher không hoạt động</p>
            </div>
        </div>
        <div class="publisher-stat-card">
            <div class="publisher-stat-icon growth">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="publisher-stat-content">
                <h3>{{ $stats['this_month'] }}</h3>
                <p>Publisher mới tháng này</p>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="publisher-filters-card">
        <form method="GET" action="{{ route('admin.users.publishers') }}" class="publisher-filters-form">
            <div class="publisher-filters-row">
                <div class="publisher-filter-group">
                    <label for="search">Tìm kiếm:</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                           placeholder="Tên publisher hoặc email..." class="publisher-filter-input">
                </div>
                <div class="publisher-filter-group">
                    <label for="status">Trạng thái:</label>
                    <select id="status" name="status" class="publisher-filter-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                    </select>
                </div>
                <div class="publisher-filter-actions">
                    <button type="submit" class="publisher-btn publisher-btn-primary">
                        <i class="fas fa-search"></i>
                        Lọc
                    </button>
                    <a href="{{ route('admin.users.publishers') }}" class="publisher-btn publisher-btn-outline">
                        <i class="fas fa-times"></i>
                        Xóa bộ lọc
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Publisher Users Table -->
    <div class="publisher-management-card">
        <div class="publisher-card-header">
            <div class="publisher-card-header-left">
                <h3>Danh sách publisher</h3>
                <span class="publisher-total-count">{{ $publisherUsers->total() }} publisher</span>
            </div>
        </div>

        <div class="publisher-card-body">
            @if($publisherUsers->count() > 0)
                <div class="publisher-table-wrapper">
                    <table class="publisher-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Thông tin publisher</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($publisherUsers as $publisherUser)
                            <tr>
                                <td>{{ $publisherUser->id }}</td>
                                <td>
                                    <div class="publisher-info">
                                        <div class="publisher-avatar">
                                            @if($publisherUser->avatar)
                                                <img src="{{ asset('storage/' . $publisherUser->avatar) }}" 
                                                     alt="{{ $publisherUser->name }}" 
                                                     class="publisher-avatar-img">
                                            @else
                                                <div class="publisher-avatar-placeholder">
                                                    <i class="fas fa-bullhorn"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="publisher-details">
                                            <div class="publisher-name">{{ $publisherUser->name }}</div>
                                            <div class="publisher-email">{{ $publisherUser->email }}</div>
                                            <div class="publisher-role">
                                                <span class="publisher-role-badge">
                                                    <i class="fas fa-bullhorn"></i> Publisher
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="publisher-status-badge {{ $publisherUser->is_active ? 'active' : 'inactive' }}">
                                        {{ $publisherUser->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="publisher-date">
                                        <div class="publisher-date-day">{{ $publisherUser->created_at->format('d/m/Y') }}</div>
                                        <div class="publisher-date-time">{{ $publisherUser->created_at->format('H:i') }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="publisher-action-buttons">
                                        <a href="{{ route('admin.users.show', $publisherUser) }}" 
                                           class="publisher-btn publisher-btn-sm publisher-btn-outline-info" 
                                           title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $publisherUser) }}" 
                                           class="publisher-btn publisher-btn-sm publisher-btn-outline-primary" 
                                           title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($publisherUser->id !== auth()->id())
                                            <button type="button" 
                                                    class="publisher-btn publisher-btn-sm publisher-btn-outline-warning" 
                                                    title="{{ $publisherUser->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}"
                                                    onclick="showTogglePublisherStatusConfirm('{{ $publisherUser->id }}', '{{ $publisherUser->name }}', {{ $publisherUser->is_active ? 'true' : 'false' }})">
                                                <i class="fas fa-{{ $publisherUser->is_active ? 'ban' : 'check' }}"></i>
                                            </button>
                                            <button type="button" 
                                                    class="publisher-btn publisher-btn-sm publisher-btn-outline-danger" 
                                                    title="Xóa"
                                                    onclick="showDeletePublisherConfirm('{{ $publisherUser->id }}', '{{ $publisherUser->name }}')">
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
                <div class="publisher-pagination-wrapper">
                    {{ $publisherUsers->links() }}
                </div>
            @else
                <div class="publisher-empty-state">
                    <div class="publisher-empty-state-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3>Chưa có publisher nào</h3>
                    <p>Bắt đầu tạo publisher đầu tiên để quản lý hệ thống</p>
                    <a href="{{ route('admin.users.create') }}?role=publisher" class="publisher-btn publisher-btn-primary">
                        <i class="fas fa-plus"></i>
                        Tạo publisher đầu tiên
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Hidden Forms for Actions -->
<form id="toggle-publisher-status-form" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<form id="delete-publisher-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function showTogglePublisherStatusConfirm(publisherId, publisherName, isActive) {
    const action = isActive ? 'vô hiệu hóa' : 'kích hoạt';
    const actionText = isActive ? 'Vô hiệu hóa' : 'Kích hoạt';
    
    showConfirmPopup({
        title: `${actionText} publisher`,
        message: `Bạn có chắc chắn muốn ${action} publisher này?`,
        details: `Publisher: ${publisherName}`,
        type: 'warning',
        confirmText: actionText,
        onConfirm: () => {
            const form = document.getElementById('toggle-publisher-status-form');
            form.action = `{{ route('admin.users.index') }}/${publisherId}/toggle-status`;
            form.submit();
        }
    });
}

function showDeletePublisherConfirm(publisherId, publisherName) {
    showConfirmPopup({
        title: 'Xóa publisher',
        message: 'Bạn có chắc chắn muốn xóa publisher này? Hành động này không thể hoàn tác.',
        details: `Publisher: ${publisherName}`,
        type: 'danger',
        confirmText: 'Xóa',
        onConfirm: () => {
            const form = document.getElementById('delete-publisher-form');
            form.action = `{{ route('admin.users.index') }}/${publisherId}`;
            form.submit();
        }
    });
}
</script>
@endsection
