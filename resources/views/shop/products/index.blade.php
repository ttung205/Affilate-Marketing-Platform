@extends('shop.layouts.app')

@section('title', 'Quản lý Sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/shop/products.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/confirm-popup.css') }}">
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
    <!-- Export -->
    <a href="{{ route('shop.products.export-excel') }}" class="btn btn-success">
    <i class="fas fa-file-export"></i> Xuất Excel
    </a>

    <!-- Import -->
    <form action="{{ route('shop.products.import-excel') }}" method="POST" enctype="multipart/form-data" style="display:inline-block;">
       @csrf
       <input type="file" name="file" id="fileInput" style="display: none;" accept=".xlsx,.csv" required>
        <button type="button" class="btn btn-primary" id="importButton">
        <i class="fas fa-file-import"></i> Nhập Excel
    </button>
    </form>

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
                            <th>Hoa hồng</th>
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
                                <span class="commission-badge">
                                    {{ $product->commission_rate ?? 0 }}%
                                </span>
                            </td>
                            <td>
                                <span class="status-badge {{ $product->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $product->is_active ? 'Đang hoạt động' : 'Vô hiệu hóa' }}
                                </span>
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
                                    <button type="button" 
                                            class="action-btn toggle" 
                                            title="{{ $product->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}"
                                            onclick="toggleProductStatus('{{ $product->id }}', '{{ $product->name }}', {{ $product->is_active ? 'true' : 'false' }})">
                                        <i class="fas fa-{{ $product->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                    <button type="button" 
                                            class="action-btn delete" 
                                            title="Xóa sản phẩm"
                                            onclick="deleteProduct('{{ $product->id }}', '{{ $product->name }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Custom Pagination -->
            <div class="custom-pagination-container">
                @if($products->hasPages())
                    <nav class="custom-pagination-nav" aria-label="Product navigation">
                        <ul class="custom-pagination-list">
                            {{-- Previous Page Link --}}
                            @if($products->onFirstPage())
                                <li class="custom-pagination-item custom-pagination-disabled">
                                    <span class="custom-pagination-link custom-pagination-arrow">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                </li>
                            @else
                                <li class="custom-pagination-item">
                                    <a href="{{ $products->appends(request()->query())->previousPageUrl() }}" class="custom-pagination-link custom-pagination-arrow" rel="prev">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                                @if($page == $products->currentPage())
                                    <li class="custom-pagination-item custom-pagination-active">
                                        <span class="custom-pagination-link">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="custom-pagination-item">
                                        <a href="{{ $products->appends(request()->query())->url($page) }}" class="custom-pagination-link">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if($products->hasMorePages())
                                <li class="custom-pagination-item">
                                    <a href="{{ $products->appends(request()->query())->nextPageUrl() }}" class="custom-pagination-link custom-pagination-arrow" rel="next">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="custom-pagination-item custom-pagination-disabled">
                                    <span class="custom-pagination-link custom-pagination-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                </li>
                            @endif
                        </ul>
                        
                        <div class="custom-pagination-info">
                            <span class="custom-pagination-text">
                                Hiển thị {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} 
                                trong tổng số {{ $products->total() }} sản phẩm
                            </span>
                        </div>
                    </nav>
                @endif
            </div>
        @else
            @if(request()->hasAny(['search', 'category', 'status']))
                <!-- No search results -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Không tìm thấy kết quả</h3>
                    <p>Không có sản phẩm nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                </div>
            @else
                <!-- Empty state - no items at all -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <h3>Chưa có sản phẩm nào</h3>
                    <p>Bắt đầu tạo sản phẩm đầu tiên để quản lý cửa hàng.</p>
                </div>
            @endif
        @endif
    </div>
</div>

@include('components.alerts')
@endsection

@push('scripts')
<script src="{{ asset('js/components/confirm-popup.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    document.getElementById('category').addEventListener('change', function() {
        this.closest('form').submit();
    });
    
    document.getElementById('status').addEventListener('change', function() {
        this.closest('form').submit();
    });
        const importButton = document.getElementById('importButton');
    const fileInput = document.getElementById('fileInput');

    importButton.addEventListener('click', function() {
        fileInput.click(); 
    });

 fileInput.addEventListener('change', function() {
    if(fileInput.files.length > 0){
        const form = fileInput.closest('form');
        form.action = "{{ route('shop.products.preview-import') }}"; // route preview
        form.submit();
    }
});

});

// Toggle product status with confirm popup
function toggleProductStatus(productId, productName, currentStatus) {
    const action = currentStatus ? 'vô hiệu hóa' : 'kích hoạt';
    const type = currentStatus ? 'warning' : 'success';
    const icon = currentStatus ? 'ban' : 'check';
    
    showConfirmPopup({
        title: `${currentStatus ? 'Vô hiệu hóa' : 'Kích hoạt'} sản phẩm`,
        message: `Bạn có chắc chắn muốn ${action} sản phẩm "${productName}"?`,
        type: type,
        confirmText: currentStatus ? 'Vô hiệu hóa' : 'Kích hoạt',
        cancelText: 'Hủy bỏ',
        onConfirm: function() {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/shop/products/${productId}/toggle-status`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'PATCH';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Delete product with confirm popup
function deleteProduct(productId, productName) {
    showConfirmPopup({
        title: 'Xóa sản phẩm',
        message: `Bạn có chắc chắn muốn xóa sản phẩm "${productName}"? Hành động này không thể hoàn tác.`,
        type: 'danger',
        confirmText: 'Xóa sản phẩm',
        cancelText: 'Hủy bỏ',
        onConfirm: function() {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/shop/products/${productId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
//click import excel



</script>
@endpush

