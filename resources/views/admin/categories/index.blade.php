@extends('components.dashboard.layout')

@section('title', 'Quản lý Danh mục')

@section('content')
<div class="category-management-content">
    <div class="category-management-header">
        <div class="category-header-left">
            <h1 class="category-page-title">Quản lý Danh mục</h1>
            <p class="category-page-description">Quản lý các danh mục sản phẩm trong hệ thống</p>
        </div>
        <div class="category-header-right">
            <a href="{{ route('admin.categories.create') }}" class="category-btn category-btn-primary">
                <i class="fas fa-plus"></i>
                Thêm danh mục mới
            </a>
        </div>
    </div>

    <div class="category-management-card">
        <div class="category-card-header">
            <div class="category-card-header-left">
                <span class="category-total-count">{{ $categories->total() }} danh mục</span>
            </div>
        </div>

        <div class="category-card-body">
            @if($categories->count() > 0)
                <div class="category-table-wrapper">
                    <table class="category-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hình ảnh</th>
                                <th>Tên danh mục</th>
                                <th>Mô tả</th>
                                <th>Số sản phẩm</th>
                                <th>Thứ tự</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                                <td>
                                    @if($category->image)
                                        <img src="{{ get_image_url($category->image) }}" 
                                             alt="{{ $category->name }}" 
                                             class="category-table-image">
                                    @else
                                        <div class="category-no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="category-name">{{ $category->name }}</div>
                                    <div class="category-slug">{{ $category->slug }}</div>
                                </td>
                                <td>
                                    <div class="category-description">
                                        {{ Str::limit($category->description, 50) ?: 'Không có mô tả' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="category-products-count">{{ $category->products_count }}</span>
                                </td>
                                <td>{{ $category->sort_order }}</td>
                                <td>
                                    <span class="category-status-badge {{ $category->is_active ? 'active' : 'inactive' }}">
                                        {{ $category->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="category-action-buttons">
                                        <a href="{{ route('admin.categories.edit', $category) }}" 
                                           class="category-btn category-btn-sm category-btn-outline-primary" 
                                           title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="category-btn category-btn-sm category-btn-outline-danger" 
                                                title="Xóa"
                                                onclick="showDeleteCategoryConfirm('{{ $category->id }}', '{{ $category->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="category-pagination-wrapper">
                    {{ $categories->links() }}
                </div>
            @else
                @if(request()->hasAny(['search', 'status']))
                    <!-- No search results -->
                    <div class="category-no-results-state">
                        <div class="category-no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Không tìm thấy kết quả</h3>
                        <p>Không có danh mục nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                    </div>
                @else
                    <!-- Empty state - no items at all -->
                    <div class="category-empty-state">
                        <div class="category-empty-state-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <h3>Chưa có danh mục nào</h3>
                        <p>Bắt đầu tạo danh mục đầu tiên để quản lý sản phẩm tốt hơn</p>
                        <a href="{{ route('admin.categories.create') }}" class="category-btn category-btn-primary">
                            <i class="fas fa-plus"></i>
                            Tạo danh mục đầu tiên
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Hidden Form for Delete Action -->
<form id="delete-category-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function showDeleteCategoryConfirm(categoryId, categoryName) {
    showConfirmPopup({
        title: 'Xóa danh mục',
        message: 'Bạn có chắc chắn muốn xóa danh mục này? Hành động này không thể hoàn tác.',
        details: `Danh mục: ${categoryName}`,
        type: 'danger',
        confirmText: 'Xóa',
        onConfirm: () => {
            const form = document.getElementById('delete-category-form');
            form.action = `{{ route('admin.categories.index') }}/${categoryId}`;
            form.submit();
        }
    });
}
</script>
@endsection