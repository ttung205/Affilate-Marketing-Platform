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
                            <div class="product-image-container">
                                <a href="{{ route('publisher.products.show', $product) }}" class="product-image-link">
                                    <img src="{{ get_image_url($product->image) }}" 
                                         alt="{{ $product->name }}" 
                                         class="product-image">
                                </a>
                            </div>
                            
                            <div class="product-content">
                                <h3 class="product-name">
                                    <a href="{{ route('publisher.products.show', $product) }}" class="product-name-link">
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                
                                <div class="product-price-row">
                                    <span class="product-price">{{ number_format($product->price, 0, ',', '.') }} VNĐ</span>
                                    <span class="commission-rate">{{ $product->commission_rate ?? 0 }}% HH</span>
                                </div>
                                
                                <button class="share-link-btn" onclick="openSharePopup({{ $product->id }}, '{{ $product->name }}', '{{ number_format($product->price, 0, ',', '.') }} VNĐ', '{{ $product->commission_rate ?? 0 }}', '{{ route('publisher.products.show', $product) }}')">
                                    <i class="fas fa-share-alt"></i>
                                    <span>LẤY LINK</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                @if(request()->hasAny(['search', 'category', 'min_price', 'max_price', 'min_commission']))
                    <!-- No search results -->
                    <div class="no-results">
                        <div class="no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="no-results-title">Không tìm thấy kết quả</h3>
                        <p class="no-results-description">
                            Không có sản phẩm nào phù hợp với tiêu chí tìm kiếm của bạn.
                        </p>
                    </div>
                @else
                    <!-- Empty state - no items at all -->
                    <div class="empty-state-container">
                        <div class="empty-state-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3 class="empty-state-title">Chưa có sản phẩm nào</h3>
                        <p class="empty-state-description">
                            Hãy đợi các shop đăng sản phẩm mới để tiếp thị và kiếm hoa hồng.
                        </p>
                        <div class="empty-state-actions">
                            <a href="{{ route('publisher.dashboard') }}" class="empty-state-btn">
                                <i class="fas fa-home"></i>
                                <span>Về Dashboard</span>
                            </a>
                            <a href="{{ route('publisher.affiliate-links.index') }}" class="empty-state-btn secondary">
                                <i class="fas fa-link"></i>
                                <span>Xem Affiliate Links</span>
                            </a>
                        </div>
                    </div>
                @endif
            @endif
        </div>
        
        <!-- Share Link Popup -->
        <div id="shareLinkPopup" class="share-popup-overlay">
            <div class="share-popup">
                <div class="share-popup-header">
                    <h3>Chia sẻ sản phẩm</h3>
                    <button class="close-popup-btn" onclick="closeSharePopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="share-popup-content">
                    <div class="product-preview">
                        <img id="popupProductImage" src="" alt="" class="popup-product-image">
                        <div class="product-preview-info">
                            <h4 id="popupProductName"></h4>
                            <div class="popup-price-row">
                                <span id="popupProductPrice" class="popup-price"></span>
                                <span id="popupCommissionRate" class="popup-commission"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="affiliate-link-section">
                        <label for="affiliateLinkInput">Link tiếp thị của bạn:</label>
                        <div class="link-input-group">
                            <input type="text" id="affiliateLinkInput" readonly class="affiliate-link-input">
                            <button class="copy-link-btn" id="copyLinkBtn" onclick="copyAffiliateLink()">
                                <i class="fas fa-copy"></i>
                                <span>Sao chép</span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="share-popup-footer">
                    <button class="share-popup-close-btn" onclick="closeSharePopup()">
                        Đóng
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if($products->count() > 0)
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

function openSharePopup(productId, productName, productPrice, commissionRate, productUrl) {
    // Show loading state
    document.getElementById('shareLinkPopup').style.display = 'flex';
    document.getElementById('affiliateLinkInput').value = 'Đang tạo link...';
    document.getElementById('copyLinkBtn').disabled = true;
    
    // Get product image from the card
    const productCard = document.querySelector(`[onclick*="${productId}"]`).closest('.product-card');
    const productImage = productCard.querySelector('.product-image');
    
    // Update product preview immediately
    document.getElementById('popupProductImage').src = productImage.src;
    document.getElementById('popupProductImage').alt = productName;
    document.getElementById('popupProductName').textContent = productName;
    document.getElementById('popupProductPrice').textContent = productPrice;
    document.getElementById('popupCommissionRate').textContent = commissionRate + '% HH';
    
    // Call API to create affiliate link
    fetch(`/publisher/products/${productId}/affiliate-link`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update popup with real affiliate link
            document.getElementById('affiliateLinkInput').value = data.affiliate_link;
            document.getElementById('copyLinkBtn').disabled = false;
            
            // Store affiliate link for copy function
            window.currentAffiliateLink = data.affiliate_link;
        } else {
            // Show error message
            document.getElementById('affiliateLinkInput').value = 'Lỗi: ' + data.message;
            document.getElementById('copyLinkBtn').disabled = true;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('affiliateLinkInput').value = 'Lỗi: Không thể tạo link';
        document.getElementById('copyLinkBtn').disabled = true;
    });
}

function closeSharePopup() {
    document.getElementById('shareLinkPopup').style.display = 'none';
    // Reset form
    document.getElementById('affiliateLinkInput').value = '';
    document.getElementById('copyLinkBtn').disabled = false;
    // Reset product info
    document.getElementById('popupProductName').textContent = '';
    document.getElementById('popupProductPrice').textContent = '';
    document.getElementById('popupCommissionRate').textContent = '';
    document.getElementById('popupProductImage').src = '';
}

function copyAffiliateLink() {
    const affiliateLink = document.getElementById('affiliateLinkInput').value;
    
    if (affiliateLink && !affiliateLink.includes('Lỗi') && !affiliateLink.includes('Đang tạo')) {
        navigator.clipboard.writeText(affiliateLink).then(function() {
            showCopySuccess();
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = affiliateLink;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showCopySuccess();
        });
    }
}

function showCopySuccess() {
    const copyBtn = document.getElementById('copyLinkBtn');
    const originalText = copyBtn.innerHTML;
    
    copyBtn.innerHTML = '<i class="fas fa-check"></i> Đã sao chép!';
    copyBtn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
    
    setTimeout(() => {
        copyBtn.innerHTML = originalText;
        copyBtn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
    }, 2000);
}

// Close popup when clicking outside
document.getElementById('shareLinkPopup').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSharePopup();
    }
});

// Close popup when pressing Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSharePopup();
    }
});
</script>
@endpush
