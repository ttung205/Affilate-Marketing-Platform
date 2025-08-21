@extends('publisher.layouts.app')

@section('title', 'Khám phá Sản phẩm')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/publisher/products.css') }}">
@endpush

@section('content')
<div class="product-management-content">
    <div class="product-management-header">
        <div class="product-management-title">
            <h2>Khám phá Sản phẩm</h2>
            <p>Tìm kiếm sản phẩm phù hợp để tiếp thị và kiếm hoa hồng</p>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="product-filters-card">
        <div class="product-filters-header">
            <h5 class="product-filters-title">Bộ lọc & Tìm kiếm</h5>
        </div>
        <div class="product-filters-body">
            <form method="GET" action="{{ route('publisher.products.index') }}" class="product-filters-form">
                <div class="product-filters-row">
                    <div class="product-filter-group">
                        <label for="search" class="product-filter-label">Tìm kiếm</label>
                        <input type="text" 
                               id="search"
                               name="search" 
                               class="product-filter-input" 
                               placeholder="Tên sản phẩm, mô tả..." 
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="product-filter-group">
                        <label for="category" class="product-filter-label">Danh mục</label>
                        <select id="category" name="category" class="product-filter-select">
                            <option value="">Tất cả danh mục</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="product-filter-group">
                        <label for="min_price" class="product-filter-label">Giá tối thiểu</label>
                        <input type="number" 
                               id="min_price"
                               name="min_price" 
                               class="product-filter-input" 
                               placeholder="0" 
                               min="0"
                               value="{{ request('min_price') }}">
                    </div>
                    
                    <div class="product-filter-group">
                        <label for="max_price" class="product-filter-label">Giá tối đa</label>
                        <input type="number" 
                               id="max_price"
                               name="max_price" 
                               class="product-filter-input" 
                               placeholder="1000000" 
                               min="0"
                               value="{{ request('max_price') }}">
                    </div>
                    
                    <div class="product-filter-group">
                        <label for="min_commission" class="product-filter-label">Hoa hồng tối thiểu (%)</label>
                        <input type="number" 
                               id="min_commission"
                               name="min_commission" 
                               class="product-filter-input" 
                               placeholder="0" 
                               min="0"
                               max="100"
                               step="0.1"
                               value="{{ request('min_commission') }}">
                    </div>
                    
                    <div class="product-filter-actions">
                        <button type="submit" class="product-btn product-btn-primary">
                            <i class="fas fa-search"></i>
                            <span>Tìm kiếm</span>
                        </button>
                        <a href="{{ route('publisher.products.index') }}" class="product-btn product-btn-secondary">
                            <i class="fas fa-times"></i>
                            <span>Xóa bộ lọc</span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="product-management-card">
        <div class="product-management-card-header">
            <h5 class="product-card-title">Danh sách sản phẩm</h5>
            <span class="product-total-count">{{ $products->total() }} sản phẩm</span>
        </div>

        <div class="product-management-card-body">
            @if($products->count() > 0)
                <div class="products-grid">
                    @foreach($products as $product)
                    <div class="product-card">
                        <div class="product-image">
                            <img src="{{ get_image_url($product->image) }}" alt="{{ $product->name }}">
                            <div class="product-overlay">
                                <a href="{{ route('publisher.products.show', $product) }}" class="view-btn">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                            <div class="product-badges">
                                <span class="commission-badge">{{ $product->commission_rate ?? 0 }}%</span>
                            </div>
                        </div>
                        
                        <div class="product-info">
                            <h3 class="product-name">{{ Str::limit($product->name, 50) }}</h3>
                            <p class="product-description">{{ Str::limit($product->description, 80) }}</p>
                            
                            <div class="product-meta">
                                <span class="category-badge">
                                    {{ $product->category->name ?? 'Không phân loại' }}
                                </span>
                                <span class="commission-badge">
                                    {{ $product->commission_rate ?? 0 }}% hoa hồng
                                </span>
                            </div>
                            
                            <div class="product-footer">
                                <span class="price">{{ $product->formatted_price }}</span>
                                <span class="shop-name">bởi {{ $product->shopOwner->name ?? 'Unknown' }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                @if(request()->hasAny(['search', 'category', 'min_price', 'max_price', 'min_commission']))
                    <!-- No search results -->
                    <div class="product-no-results-state">
                        <div class="product-no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Không tìm thấy kết quả</h3>
                        <p>Không có sản phẩm nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                        <a href="{{ route('publisher.products.index') }}" class="clear-filter-btn">
                            <i class="fas fa-times"></i>
                            <span>Xóa bộ lọc</span>
                        </a>
                    </div>
                @else
                    <!-- Empty state - no items at all -->
                    <div class="product-empty-state">
                        <div class="product-empty-state-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3>Chưa có sản phẩm nào</h3>
                        <p>Hãy đợi các shop đăng sản phẩm mới để tiếp thị.</p>
                    </div>
                @endif
            @endif
        </div>
        
        <!-- Pagination -->
        @if($products->count() > 0)
            <div class="product-pagination-wrapper">
                {{ $products->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    document.getElementById('category').addEventListener('change', function() {
        this.closest('form').submit();
    });
    
    document.getElementById('min_price').addEventListener('change', function() {
        this.closest('form').submit();
    });
    
    document.getElementById('max_price').addEventListener('change', function() {
        this.closest('form').submit();
    });
    
    document.getElementById('min_commission').addEventListener('change', function() {
        this.closest('form').submit();
    });
});
</script>
@endpush
