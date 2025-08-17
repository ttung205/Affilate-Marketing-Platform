@extends('components.dashboard.layout')

@section('title', 'Thêm Danh mục mới')

@section('content')
<div class="category-management-content">
    <div class="category-management-header">
        <div class="header-left">
            <h1 class="page-title">Thêm Danh mục mới</h1>
            <p class="page-description">Tạo danh mục sản phẩm mới trong hệ thống</p>
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
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="category-form">
                @csrf
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label">Tên danh mục <span class="required">*</span></label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
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
                               value="{{ old('sort_order', 0) }}" 
                               class="form-input @error('sort_order') is-invalid @enderror" 
                               placeholder="0"
                               min="0">
                        @error('sort_order')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="description" class="form-label">Mô tả</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-textarea @error('description') is-invalid @enderror" 
                              placeholder="Nhập mô tả danh mục (không bắt buộc)"
                              rows="4">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="image" class="form-label">Hình ảnh danh mục</label>
                        <input type="file" 
                               id="image" 
                               name="image" 
                               class="form-file @error('image') is-invalid @enderror" 
                               accept="image/*">
                        <div class="form-help">Chọn file hình ảnh (JPG, PNG, GIF) - Tối đa 2MB</div>
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
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <span class="checkmark"></span>
                                Hoạt động
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary1">
                        <i class="fas fa-save"></i>
                        Tạo danh mục
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