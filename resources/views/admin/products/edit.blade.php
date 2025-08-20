@extends('components.dashboard.layout')

@section('title', 'Chỉnh sửa sản phẩm - Admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/products.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/forms.css') }}">
@endpush

@section('content')
<div class="product-management-content">
    <div class="product-management-header">
        <div class="product-management-title">
            <h2>Chỉnh sửa sản phẩm</h2>
            <p>Cập nhật thông tin sản phẩm: {{ $product->name }}</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="product-back-btn">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
    
    <div class="product-form-container">
        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="product-form">
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
                    @if($product->image)
                        <div class="current-image">
                            <img src="{{ get_image_url($product->image) }}" alt="Current image" class="product-thumbnail">
                            <small>Hình ảnh hiện tại</small>
                                                    <div class="product-image-actions">
                            <button type="button"
                                    class="product-btn product-btn-sm product-btn-outline-danger"
                                    onclick="removeProductImage({{ $product->id }})">
                                <i class="fas fa-trash"></i>
                                Xóa ảnh
                            </button>
                        </div>
                        </div>
                    @endif
                    <input type="file" 
                           id="image" 
                           name="image" 
                           class="product-image-input" 
                           accept="image/*"
                           style="display: none;">
                    <label for="image" class="product-image-upload-btn">
                        <i class="fas fa-upload"></i>
                        {{ $product->image ? 'Thay đổi ảnh' : 'Chọn ảnh' }}
                    </label>
                    <div class="product-image-help">
                        Chọn file hình ảnh mới (JPG, PNG, GIF) - Tối đa 2MB
                        @if($product->image)
                            <br><small>Để trống nếu muốn giữ hình ảnh hiện tại</small>
                        @endif
                    </div>
                    @error('image')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="form-group full-width">
                <label for="description" class="form-label">Mô tả sản phẩm</label>
                <textarea id="description" 
                          name="description" 
                          class="form-textarea" 
                          rows="4">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group full-width">
                <label for="affiliate_link" class="form-label">Link affiliate</label>
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
            
            <div class="form-group full-width">
                <label class="form-checkbox-label">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1" 
                           {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                    <span class="checkmark"></span>
                    Kích hoạt sản phẩm
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="form-submit-btn">
                    <i class="fas fa-save"></i> Cập nhật sản phẩm
                </button>
                <a href="{{ route('admin.products.index') }}" class="form-cancel-btn">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>
@endsection

<script>
function removeProductImage(productId) {
    showConfirmPopup({
        title: 'Xóa ảnh sản phẩm',
        message: 'Bạn có chắc chắn muốn xóa ảnh sản phẩm? Ảnh sẽ được thay thế bằng ảnh mặc định.',
        type: 'danger',
        confirmText: 'Xóa ảnh',
        onConfirm: () => {
            fetch(`/admin/products/${productId}/image`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cập nhật preview
                    const currentImage = document.querySelector('.current-image');
                    currentImage.innerHTML = `
                        <img src="${data.image_url}" alt="Default image" class="product-thumbnail">
                        <small>Ảnh mặc định</small>
                    `;
                    
                    // Ẩn nút xóa
                    const removeBtn = document.querySelector('.product-image-actions');
                    if (removeBtn) {
                        removeBtn.style.display = 'none';
                    }
                    
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
</script>