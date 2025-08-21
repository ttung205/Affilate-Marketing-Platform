@extends('shop.layouts.app')

@section('title', 'Chi tiết sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/shop/products.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/confirm-popup.css') }}">
@endpush

@section('content')
<div class="products-container">
    <!-- Header Section -->
    <div class="products-header">
        <h1>Chi tiết sản phẩm</h1>
        <div class="header-actions">
            <a href="{{ route('shop.products.edit', $product) }}" class="add-product-btn">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <a href="{{ route('shop.products.index') }}" class="clear-filter-btn">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <!-- Product Details -->
    <div class="product-details-container">
        <div class="product-details-grid">
            <!-- Product Image Section -->
            <div class="product-image-section">
                <div class="product-main-image">
                    <img src="{{ get_image_url($product->image) }}" 
                         alt="{{ $product->name }}" 
                         class="product-detail-image">
                </div>
                <div class="product-status-badge {{ $product->is_active ? 'status-active' : 'status-inactive' }}">
                    {{ $product->is_active ? 'Đang hoạt động' : 'Vô hiệu hóa' }}
                </div>
            </div>

            <!-- Product Info Section -->
            <div class="product-info-section">
                <div class="product-header">
                    <h2 class="product-title">{{ $product->name }}</h2>
                    <div class="product-meta">
                        <span class="product-id">ID: #{{ $product->id }}</span>
                        <span class="product-date">Tạo: {{ $product->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <div class="product-description">
                    <h3>Mô tả sản phẩm</h3>
                    <p>{{ $product->description ?: 'Chưa có mô tả' }}</p>
                </div>

                <div class="product-details-grid-info">
                    <div class="detail-item">
                        <span class="detail-label">Danh mục:</span>
                        <span class="detail-value">
                            @if($product->category)
                                <span class="category-badge">{{ $product->category->name }}</span>
                            @else
                                <span class="text-muted">Không phân loại</span>
                            @endif
                        </span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Giá sản phẩm:</span>
                        <span class="detail-value price-value">{{ $product->formatted_price }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Tồn kho:</span>
                        <span class="detail-value">
                            <span class="stock-badge {{ $product->stock > 10 ? 'stock-high' : ($product->stock > 0 ? 'stock-medium' : 'stock-low') }}">
                                {{ $product->stock }}
                            </span>
                        </span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Tỷ lệ hoa hồng:</span>
                        <span class="detail-value">
                            <span class="commission-badge">{{ $product->commission_rate ?? 0 }}%</span>
                        </span>
                    </div>

                    @if($product->affiliate_link)
                    <div class="detail-item">
                        <span class="detail-label">Original Link:</span>
                        <span class="detail-value">
                            <a href="{{ $product->affiliate_link }}" target="_blank" class="link-value">
                                {{ Str::limit($product->affiliate_link, 50) }}
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </span>
                    </div>
                    @endif

                    <div class="detail-item">
                        <span class="detail-label">Cập nhật lần cuối:</span>
                        <span class="detail-value">{{ $product->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="product-actions">
                    <a href="{{ route('shop.products.edit', $product) }}" class="action-btn edit">
                        <i class="fas fa-edit"></i>
                        Chỉnh sửa
                    </a>
                    <button type="button" 
                            class="action-btn toggle" 
                            onclick="toggleProductStatus('{{ $product->id }}', '{{ $product->name }}', {{ $product->is_active ? 'true' : 'false' }})">
                        <i class="fas fa-{{ $product->is_active ? 'ban' : 'check' }}"></i>
                        {{ $product->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}
                    </button>
                    <button type="button" 
                            class="action-btn delete" 
                            onclick="deleteProduct('{{ $product->id }}', '{{ $product->name }}')">
                        <i class="fas fa-trash"></i>
                        Xóa sản phẩm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@include('components.alerts')
@endsection

@push('scripts')
<script src="{{ asset('js/components/confirm-popup.js') }}"></script>
<script>
// Toggle product status with confirm popup
function toggleProductStatus(productId, productName, currentStatus) {
    const action = currentStatus ? 'vô hiệu hóa' : 'kích hoạt';
    const type = currentStatus ? 'warning' : 'success';
    
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
</script>
@endpush
