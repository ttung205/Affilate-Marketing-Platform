@extends('components.dashboard.layout')

@section('title', 'Quản lý sản phẩm - Admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/products.css') }}">
@endpush

@section('content')
    <div class="product-management-content">
        <div class="product-management-header">
            <div class="product-management-title">
                <h2>Quản lý sản phẩm</h2>
                <p>Quản lý tất cả sản phẩm trong hệ thống</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="product-add-btn">
                <i class="fas fa-plus"></i> Thêm sản phẩm
            </a>
        </div>

        <!-- Filters Section -->
        <div class="product-filters-card">
            <div class="product-filters-header">
                <h5 class="product-filters-title">Bộ lọc & Tìm kiếm</h5>
            </div>
            <div class="product-filters-body">
                <form method="GET" action="{{ route('admin.products.index') }}" class="product-filters-form">
                    <div class="product-filters-row">
                        <div class="product-filter-group">
                            <label for="search" class="product-filter-label">Tìm kiếm</label>
                            <input type="text" 
                                   id="search"
                                   name="search" 
                                   class="product-filter-input" 
                                   placeholder="Tên sản phẩm, mô tả..." 
                                   value="{{ request('search') }}">
                        </div>
                        
                        <div class="product-filter-group">
                            <label for="category" class="product-filter-label">Danh mục</label>
                            <select id="category" name="category" class="product-filter-select">
                                <option value="">Tất cả danh mục</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="product-filter-group">
                            <label for="status" class="product-filter-label">Trạng thái</label>
                            <select id="status" name="status" class="product-filter-select">
                                <option value="">Tất cả trạng thái</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                            </select>
                        </div>
                        
                        <div class="product-filter-actions">
                            <button type="submit" class="product-btn product-btn-primary">
                                <i class="fas fa-search"></i>
                                <span>Tìm kiếm</span>
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="product-btn product-btn-secondary">
                                <i class="fas fa-times"></i>
                                <span>Xóa bộ lọc</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="product-management-card">
            <div class="product-management-card-header">
                <h5 class="product-card-title">Danh sách sản phẩm</h5>
                <span class="product-total-count">{{ $products->total() }} sản phẩm</span>
            </div>

            <div class="product-management-card-body">
                @if($products->count() > 0)
                    <table class="product-management-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hình ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>Tồn kho</th>
                                <th>Tỷ lệ hoa hồng</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>
                                        @if($product->image)
                                            <img src="{{ get_image_url($product->image) }}" alt="{{ $product->name }}"
                                                class="product-table-image">
                                        @else
                                            <div class="product-no-image">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="product-name">{{ $product->name }}</div>
                                        <div class="product-description">{{ Str::limit($product->description, 50) }}</div>
                                    </td>
                                    <td>
                                        @if($product->category)
                                            <span class="category-badge">
                                                {{ $product->category->name }}
                                            </span>
                                        @else
                                            <span class="no-category">Không có danh mục</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="product-price">{{ $product->formatted_price }}</span>
                                    </td>
                                    <td>
                                        <span class="stock-count {{ $product->stock > 0 ? 'in-stock' : 'out-of-stock' }}">
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="commission-rate-badge">
                                            {{ $product->commission_rate ?? 0 }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $product->is_active ? 'active' : 'inactive' }}">
                                            {{ $product->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="product-action-buttons">
                                            <a href="{{ route('admin.products.edit', $product) }}" class="product-btn-edit"
                                                title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <button type="button" class="product-btn-toggle"
                                                title="{{ $product->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}"
                                                onclick="showToggleProductStatusConfirm('{{ $product->id }}', '{{ $product->name }}', {{ $product->is_active ? 'true' : 'false' }})">
                                                <i class="fas fa-{{ $product->is_active ? 'ban' : 'eye' }}"></i>
                                            </button>

                                            <button type="button" class="product-btn-delete" title="Xóa"
                                                onclick="showDeleteProductConfirm('{{ $product->id }}', '{{ $product->name }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    @if(request()->hasAny(['search', 'category', 'status']))
                        <!-- No search results -->
                        <div class="product-no-results-state">
                            <div class="product-no-results-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <h3>Không tìm thấy kết quả</h3>
                            <p>Không có sản phẩm nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                        </div>
                    @else
                        <!-- Empty state - no items at all -->
                        <div class="product-empty-state">
                            <div class="product-empty-state-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <h3>Chưa có sản phẩm nào</h3>
                            <p>Bắt đầu tạo sản phẩm đầu tiên để quản lý cửa hàng.</p>
                            <a href="{{ route('admin.products.create') }}" class="product-btn product-btn-primary">
                                <i class="fas fa-plus"></i>
                                <span>Tạo sản phẩm</span>
                            </a>
                        </div>
                    @endif
                @endif
            </div>
            
            <!-- Pagination -->
            @if($products->count() > 0)
                <div class="product-pagination-wrapper">
                    {{ $products->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

<!-- Hidden Forms for Actions -->
<form id="toggle-product-status-form" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<form id="delete-product-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function showToggleProductStatusConfirm(productId, productName, isActive) {
    const action = isActive ? 'vô hiệu hóa' : 'kích hoạt';
    const actionText = isActive ? 'Vô hiệu hóa' : 'Kích hoạt';
    
    showConfirmPopup({
        title: `${actionText} sản phẩm`,
        message: `Bạn có chắc chắn muốn ${action} sản phẩm này?`,
        details: `Sản phẩm: ${productName}`,
        type: 'warning',
        confirmText: actionText,
        onConfirm: () => {
            const form = document.getElementById('toggle-product-status-form');
            form.action = `{{ route('admin.products.index') }}/${productId}/toggle-status`;
            form.submit();
        }
    });
}

function showDeleteProductConfirm(productId, productName) {
    showConfirmPopup({
        title: 'Xóa sản phẩm',
        message: 'Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.',
        details: `Sản phẩm: ${productName}`,
        type: 'danger',
        confirmText: 'Xóa',
        onConfirm: () => {
            const form = document.getElementById('delete-product-form');
            form.action = `{{ route('admin.products.index') }}/${productId}`;
            form.submit();
        }
    });
}
</script>
@endsection