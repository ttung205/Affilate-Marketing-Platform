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
                            <img src="{{ asset('storage/' . $product->image) }}" alt="Current image" class="product-thumbnail">
                            <small>Hình ảnh hiện tại</small>
                        </div>
                    @endif
                    <input type="file" id="image" name="image" class="form-file" accept="image/*">
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