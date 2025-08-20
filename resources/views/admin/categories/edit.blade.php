@extends('components.dashboard.layout')

@section('title', 'Chỉnh sửa Danh mục')

@section('content')
<div class="category-management-content">
    <div class="category-management-header">
        <div class="header-left">
            <h1 class="page-title">Chỉnh sửa Danh mục</h1>
            <p class="page-description">Cập nhật thông tin danh mục "{{ $category->name }}"</p>
        </div>
        <div class="header-right">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary1">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="category-management-card">
        <div class="category-management-card-header">
            <h3>Thông tin danh mục</h3>
        </div>

        <div class="category-management-card-body">
            <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="category-form">
                @csrf
                @method('PUT')
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label">Tên danh mục <span class="required">*</span></label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $category->name) }}" 
                               class="form-input @error('name') is-invalid @enderror" 
                               placeholder="Nhập tên danh mục"
                               required>
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sort_order" class="form-label">Thứ tự sắp xếp</label>
                        <input type="number" 
                               id="sort_order" 
                               name="sort_order" 
                               value="{{ old('sort_order', $category->sort_order) }}" 
                               class="form-input @error('sort_order') is-invalid @enderror" 
                               placeholder="0"
                               min="0">
                        @error('sort_order')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-textarea @error('description') is-invalid @enderror" 
                              placeholder="Nhập mô tả danh mục (không bắt buộc)"
                              rows="4">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="image" class="form-label">Hình ảnh danh mục</label>
                        
                        @if($category->image)
                            <div class="current-image">
                                <img src="{{ get_image_url($category->image) }}" 
                                     alt="{{ $category->name }}" 
                                     class="category-preview-image">
                                <div class="image-info">
                                    <span>Hình ảnh hiện tại</span>
                                    <small>{{ basename($category->image) }}</small>
                                </div>
                                <div class="category-image-remove-actions">
                                    <button type="button" 
                                            class="category-image-remove-btn category-image-remove-btn-sm category-image-remove-btn-danger" 
                                            onclick="removeCategoryImage({{ $category->id }})">
                                        <i class="fas fa-trash"></i>
                                        Xóa ảnh
                                    </button>
                                </div>
                            </div>
                        @endif
                        
                        <input type="file" 
                               id="image" 
                               name="image" 
                               class="category-image-input @error('image') is-invalid @enderror" 
                               accept="image/*"
                               style="display: none;">
                        <label for="image" class="category-image-upload-btn">
                            <i class="fas fa-upload"></i>
                            {{ $category->image ? 'Thay đổi ảnh' : 'Chọn ảnh' }}
                        </label>
                        <div class="category-image-help">
                            Chọn file hình ảnh mới (JPG, PNG, GIF) - Tối đa 2MB
                            @if($category->image)
                                <br><small>Để trống nếu muốn giữ hình ảnh hiện tại</small>
                            @endif
                        </div>
                        @error('image')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Trạng thái</label>
                        <div class="form-checkbox-group">
                            <label class="form-checkbox">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                <span class="checkmark"></span>
                                Hoạt động
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary1">
                        <i class="fas fa-save"></i>
                        Cập nhật danh mục
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary2">
                        Hủy bỏ
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

<script>
function removeCategoryImage(categoryId) {
    showConfirmPopup({
        title: 'Xóa ảnh danh mục',
        message: 'Bạn có chắc chắn muốn xóa ảnh danh mục? Ảnh sẽ được thay thế bằng ảnh mặc định.',
        type: 'danger',
        confirmText: 'Xóa ảnh',
        onConfirm: () => {
            fetch(`/admin/categories/${categoryId}/image`, {
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
                        <img src="${data.image_url}" alt="Default image" class="category-preview-image">
                        <div class="image-info">
                            <span>Ảnh mặc định</span>
                            <small>default-category.svg</small>
                        </div>
                    `;
                    
                    // Ẩn nút xóa
                    const removeBtn = document.querySelector('.category-image-remove-actions');
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