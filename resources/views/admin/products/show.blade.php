@extends('components.dashboard.layout')

@section('title', 'Chi tiết sản phẩm - Admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/product-show.css') }}">
@endpush

@section('content')
    <div class="product-detail-container">
        <div class="product-detail-header">
            <div class="product-detail-title">
                <h2>Chi tiết sản phẩm</h2>
                <p>Thông tin chi tiết về sản phẩm</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="product-back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="product-detail-card">
            <div class="product-detail-body">
                <div class="product-detail-row">
                    <!-- Product Image -->
                    <div class="product-image-section">
                        @if($product->image)
                            <img src="{{ get_image_url($product->image) }}" alt="{{ $product->name }}"
                                class="product-detail-image">
                        @else
                            <div class="product-detail-no-image">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif
                    </div>

                    <!-- Product Information -->
                    <div class="product-info-section">
                        <!-- ID Sản phẩm -->
                        <div class="product-info-group full-width">
                            <div class="product-info-label">ID Sản phẩm</div>
                            <div class="product-info-value">#{{ $product->id }}</div>
                        </div>

                        <!-- Tên Shop -->
                        <div class="product-info-group full-width">
                            <div class="product-info-label">Tên Shop</div>
                            <div class="product-info-value">
                                @if($product->shopOwner)
                                    <span class="shop-badge">
                                        <i class="fas fa-store"></i> {{ $product->shopOwner->name }}
                                    </span>
                                @else
                                    <span class="text-muted">Không có thông tin shop</span>
                                @endif
                            </div>
                        </div>

                        <!-- Tên sản phẩm và Danh mục (1 dòng) -->
                        <div class="product-info-row">
                            <div class="product-info-group">
                                <div class="product-info-label">Tên sản phẩm</div>
                                <div class="product-info-value large">{{ $product->name }}</div>
                            </div>

                            <div class="product-info-group">
                                <div class="product-info-label">Danh mục</div>
                                <div class="product-info-value">
                                    @if($product->category)
                                        <span class="category-badge">{{ $product->category->name }}</span>
                                    @else
                                        <span class="text-muted">Không có danh mục</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Giá và Tồn kho (1 dòng) -->
                        <div class="product-info-row">
                            <div class="product-info-group">
                                <div class="product-info-label">Giá</div>
                                <div class="product-info-value large">{{ $product->formatted_price }}</div>
                            </div>

                            <div class="product-info-group">
                                <div class="product-info-label">Tồn kho</div>
                                <div class="product-info-value">
                                    <span class="stock-badge {{ $product->stock > 0 ? 'in-stock' : 'out-of-stock' }}">
                                        {{ $product->stock }} sản phẩm
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Tỷ lệ hoa hồng và Trạng thái (1 dòng) -->
                        <div class="product-info-row">
                            <div class="product-info-group">
                                <div class="product-info-label">Tỷ lệ hoa hồng</div>
                                <div class="product-info-value">
                                    <span class="commission-badge">{{ $product->commission_rate ?? 0 }}%</span>
                                </div>
                            </div>

                            <div class="product-info-group">
                                <div class="product-info-label">Trạng thái</div>
                                <div class="product-info-value">
                                    <span class="status-badge {{ $product->is_active ? 'active' : 'inactive' }}">
                                        {{ $product->is_active ? 'Hoạt động' : 'Không hoạt động' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Ngày tạo và Cập nhật lần cuối (1 dòng) -->
                        <div class="product-info-row">
                            <div class="product-info-group">
                                <div class="product-info-label">Ngày tạo</div>
                                <div class="product-info-value">{{ $product->created_at->format('d/m/Y H:i') }}</div>
                            </div>

                            <div class="product-info-group">
                                <div class="product-info-label">Cập nhật lần cuối</div>
                                <div class="product-info-value">{{ $product->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Description -->
                <div class="product-description-section">
                    <div class="product-description-label">Mô tả sản phẩm</div>
                    <div class="product-description-text">
                        {{ $product->description ?? 'Chưa có mô tả' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

