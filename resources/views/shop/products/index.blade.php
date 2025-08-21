@extends('shop.layouts.app')

@section('title', 'Quản lý Sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/shop/products.css') }}">
@endpush

@section('content')
<div class="products-container">
    <!-- Header Section -->
    <div class="products-header">
        <h1>Quản lý Sản phẩm</h1>
        <a href="{{ route('shop.products.create') }}" class="add-product-btn">
            <i class="fas fa-plus"></i>
            Thêm Sản phẩm
        </a>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-filter-section">
        <form method="GET" action="{{ route('shop.products.index') }}" class="search-filter-form">
            <div class="form-group">
                <label for="search">Tìm kiếm</label>
                <input type="text" name="search" id="search" 
                       value="{{ request('search') }}"
                       placeholder="Tên hoặc mô tả sản phẩm..."
                       class="form-control">
            </div>
            
            <div class="form-group">
                <label for="category">Danh mục</label>
                <select name="category" id="category" class="form-control">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label for="status">Trạng thái</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Vô hiệu hóa</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                    Tìm kiếm
                </button>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="products-table-container">
        @if($products->count() > 0)
            <div class="overflow-x-auto">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>
                                <div class="product-info">
                                    <img class="product-image" 
                                         src="{{ get_image_url($product->image) }}" 
                                         alt="{{ $product->name }}">
                                    <div class="product-details">
                                        <h4>{{ $product->name }}</h4>
                                        <p>{{ Str::limit($product->description, 50) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="category-badge">
                                    {{ $product->category->name ?? 'Không phân loại' }}
                                </span>
                            </td>
                            <td class="text-sm text-gray-900">
                                {{ $product->formatted_price }}
                            </td>
                            <td>
                                <span class="stock-badge {{ $product->stock > 10 ? 'stock-high' : ($product->stock > 0 ? 'stock-medium' : 'stock-low') }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('shop.products.toggle-status', $product) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="status-toggle {{ $product->is_active ? 'status-active' : 'status-inactive' }}">
                                        {{ $product->is_active ? 'Đang hoạt động' : 'Vô hiệu hóa' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('shop.products.show', $product) }}" 
                                       class="action-btn view" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('shop.products.edit', $product) }}" 
                                       class="action-btn edit" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('shop.products.destroy', $product) }}" 
                                          class="inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="action-btn delete"
                                                title="Xóa sản phẩm"
                                                onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="pagination-container">
                {{ $products->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3>Chưa có sản phẩm nào</h3>
                <p>Bắt đầu tạo sản phẩm đầu tiên của bạn</p>
                <a href="{{ route('shop.products.create') }}" class="add-product-btn">
                    <i class="fas fa-plus"></i>
                    Thêm Sản phẩm
                </a>
            </div>
        @endif
    </div>
</div>

@include('components.alerts')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    document.getElementById('category').addEventListener('change', function() {
        this.closest('form').submit();
    });
    
    document.getElementById('status').addEventListener('change', function() {
        this.closest('form').submit();
    });

    // Add loading state to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            }
        });
    });

    // Confirm delete with better UX
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.')) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endpush
