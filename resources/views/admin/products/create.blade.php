@extends('components.dashboard.layout')

@section('title', 'Thêm sản phẩm mới - Admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/products.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/forms.css') }}">
@endpush

@section('content')
    <div class="product-management-content">
        <div class="product-management-header">
            <div class="product-management-title">
                <h2>Thêm sản phẩm mới</h2>
                <p>Tạo sản phẩm mới cho hệ thống affiliate</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="product-back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="product-form-container">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data"
                class="product-form">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label">Tên sản phẩm *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-input" required>
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="category_id" class="form-label">Danh mục</label>
                        <select id="category_id" name="category_id"
                            class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">Chọn danh mục (không bắt buộc)</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                        <input type="text" id="price" name="price" value="{{ old('price') }}" class="form-input"
                            placeholder="VD: 225205000" required>
                        <small class="form-help">Nhập số tự nhiên, không cần dấu phẩy hoặc chấm</small>
                        @error('price')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="stock" class="form-label">Số lượng tồn kho *</label>
                        <input type="number" id="stock" name="stock" value="{{ old('stock', 0) }}" class="form-input"
                            min="0" required>
                        @error('stock')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="commission_rate" class="form-label">Tỷ lệ hoa hồng (%) *</label>
                        <input type="number" id="commission_rate" name="commission_rate"
                            value="{{ old('commission_rate', 0) }}" class="form-input" min="0" max="100" step="0.01"
                            required>
                        @error('commission_rate')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="image" class="form-label">Hình ảnh sản phẩm</label>
                        <input type="file" id="image" name="image" class="form-file" accept="image/*">
                        @error('image')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="description" class="form-label">Mô tả sản phẩm</label>
                        <textarea id="description" name="description" class="form-textarea"
                            rows="4">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="affiliate_link" class="form-label">Link affiliate</label>
                        <input type="url" id="affiliate_link" name="affiliate_link" value="{{ old('affiliate_link') }}"
                            class="form-input" placeholder="https://example.com/product">
                        @error('affiliate_link')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="form-submit-btn">
                        <i class="fas fa-save"></i> Tạo sản phẩm
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="form-cancel-btn">Hủy bỏ</a>
                </div>
            </form>
        </div>
    </div>
@endsection