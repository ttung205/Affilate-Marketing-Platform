@extends('shop.layouts.app')

@section('title', 'Chỉnh sửa sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/shop/forms.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/confirm-popup.css') }}">
@endpush

@section('content')
<div class="product-management-content">
    <div class="product-management-header">
        <div class="product-management-title">
            <h2>Chỉnh sửa sản phẩm</h2>
            <p>Cập nhật thông tin sản phẩm: {{ $product->name }}</p>
        </div>
        <a href="{{ route('shop.products.index') }}" class="product-back-btn">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
    
    <div class="product-form-container">
        <form action="{{ route('shop.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="product-form" id="editProductForm">
            @csrf
            @method('PUT')
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="form-label">Tên sản phẩm *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" class="form-input" required>
                    @error('name')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="category_id" class="form-label">Danh mục</label>
                    <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                        <option value="">Chọn danh mục (không bắt buộc)</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price" class="form-label">Giá sản phẩm *</label>
                    <input type="number" 
                           id="price" 
                           name="price" 
                           value="{{ old('price', $product->price) }}" 
                           class="form-input @error('price') is-invalid @enderror" 
                           placeholder="VD: 225205000"
                           min="0"
                           required>
                    <div class="form-help">Nhập số tự nhiên, không cần dấu phẩy hoặc chấm</div>
                    @error('price')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="stock" class="form-label">Số lượng tồn kho *</label>
                    <input type="number" 
                           id="stock" 
                           name="stock" 
                           value="{{ old('stock', $product->stock) }}" 
                           class="form-input @error('stock') is-invalid @enderror" 
                           placeholder="0"
                           min="0"
                           required>
                    @error('stock')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="commission_rate" class="form-label">Tỷ lệ hoa hồng (%) *</label>
                    <input type="number" 
                           id="commission_rate" 
                           name="commission_rate" 
                           value="{{ old('commission_rate', $product->commission_rate) }}" 
                           class="form-input @error('commission_rate') is-invalid @enderror" 
                           min="0" 
                           max="100" 
                           step="0.01" 
                           required>
                    @error('commission_rate')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="image" class="form-label">Hình ảnh sản phẩm</label>
                    
                    <div class="product-image-container">
                        <div class="product-image-preview" id="productImagePreview">
                            @if($product->image)
                                <img src="{{ get_image_url($product->image) }}" alt="{{ $product->name }}" class="product-preview-img">
                                <div class="product-image-overlay">
                                    <i class="fas fa-eye"></i>
                                </div>
                            @else
                                <div class="product-image-placeholder">
                                    <i class="fas fa-image"></i>
                                    <span>Chưa có ảnh</span>
                                </div>
                            @endif
                        </div>
                        
                        <input type="file" 
                               id="image" 
                               name="image" 
                               class="product-image-input @error('image') is-invalid @enderror" 
                               accept="image/*"
                               onchange="previewProductImage(this)"
                               style="display: none;">
                        
                        <div class="product-image-actions">
                            <label for="image" class="product-image-upload-btn">
                                <i class="fas fa-upload"></i>
                                {{ $product->image ? 'Thay đổi ảnh' : 'Chọn ảnh' }}
                            </label>
                            
                            @if($product->image)
                                <button type="button" 
                                        class="product-image-remove-btn" 
                                        onclick="removeProductImage({{ $product->id }})">
                                    <i class="fas fa-trash"></i>
                                    Xóa ảnh
                                </button>
                            @endif
                        </div>
                        
                        <div class="product-image-help">
                            Chọn file hình ảnh mới (JPG, PNG, GIF) - Tối đa 2MB
                            @if($product->image)
                                <br><small>Để trống nếu muốn giữ hình ảnh hiện tại</small>
                            @endif
                        </div>
                    </div>
                    
                    @error('image')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="description" class="form-label">Mô tả sản phẩm</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-textarea" 
                              rows="4">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="affiliate_link" class="form-label">Original Link</label>
                    <input type="url" 
                           id="affiliate_link" 
                           name="affiliate_link" 
                           value="{{ old('affiliate_link', $product->affiliate_link) }}" 
                           class="form-input" 
                           placeholder="https://example.com/product">
                    @error('affiliate_link')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-checkbox-label">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        Kích hoạt sản phẩm
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="form-submit-btn" id="submitBtn">
                    <i class="fas fa-save"></i> Cập nhật sản phẩm
                </button>
                <button type="button" class="form-cancel-btn" onclick="confirmCancel()">Hủy bỏ</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/components/confirm-popup.js') }}"></script>
<script>
function removeProductImage(productId) {
    showConfirmPopup({
        title: 'Xóa ảnh sản phẩm',
        message: 'Bạn có chắc chắn muốn xóa ảnh sản phẩm? Ảnh sẽ được thay thế bằng ảnh mặc định.',
        type: 'danger',
        confirmText: 'Xóa ảnh',
        onConfirm: () => {
            fetch(`/shop/products/${productId}/image`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cập nhật preview
                    const productImagePreview = document.getElementById('productImagePreview');
                    productImagePreview.innerHTML = `
                        <img src="${data.image_url}" alt="Default image" class="product-preview-img">
                        <div class="product-image-overlay">
                            <i class="fas fa-eye"></i>
                        </div>
                    `;
                    
                    // Ẩn nút xóa
                    const removeBtn = document.querySelector('.product-image-actions .product-image-remove-btn');
                    if (removeBtn) {
                        removeBtn.style.display = 'none';
                    }
                    
                    // Cập nhật label
                    const uploadLabel = document.querySelector('.product-image-upload-btn');
                    uploadLabel.textContent = 'Chọn ảnh';
                    
                    // Hiển thị thông báo thành công
                    showConfirmPopup({
                        title: 'Thành công',
                        message: data.message,
                        type: 'success',
                        confirmText: 'OK',
                        onConfirm: () => {}
                    });
                } else {
                    // Hiển thị thông báo lỗi
                    showConfirmPopup({
                        title: 'Lỗi',
                        message: data.message,
                        type: 'danger',
                        confirmText: 'OK',
                        onConfirm: () => {}
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showConfirmPopup({
                    title: 'Lỗi',
                    message: 'Có lỗi xảy ra khi xóa ảnh',
                    type: 'danger',
                    confirmText: 'OK',
                    onConfirm: () => {}
                });
            });
        }
    });
}

function previewProductImage(input) {
    const preview = document.getElementById('productImagePreview');
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Product preview" class="product-preview-img">
                <div class="product-image-overlay">
                    <i class="fas fa-eye"></i>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        // Restore original image if exists
        @if($product->image)
            preview.innerHTML = `
                <img src="{{ get_image_url($product->image) }}" alt="{{ $product->name }}" class="product-preview-img">
                <div class="product-image-overlay">
                    <i class="fas fa-eye"></i>
                </div>
            `;
        @else
            preview.innerHTML = `
                <div class="product-image-placeholder">
                    <i class="fas fa-image"></i>
                    <span>Chưa có ảnh</span>
                </div>
            `;
        @endif
    }
}

function confirmCancel() {
    showConfirmPopup({
        title: 'Hủy bỏ chỉnh sửa',
        message: 'Bạn có chắc chắn muốn hủy bỏ? Tất cả thay đổi sẽ bị mất.',
        type: 'warning',
        confirmText: 'Hủy bỏ',
        cancelText: 'Tiếp tục',
        onConfirm: function() {
            window.location.href = '{{ route("shop.products.index") }}';
        }
    });
}

// Form submission with loading state
document.getElementById('editProductForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
});
</script>
@endpush
