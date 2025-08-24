@extends('publisher.layouts.app')

@section('title', $product->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/publisher/products.css') }}">
<link rel="stylesheet" href="{{ asset('css/components/confirm-popup.css') }}">
@endpush

@section('content')
<div class="product-detail-container">
    <!-- Header Section -->
    <div class="product-header">
        <div class="product-back-btn">
            <a href="{{ route('publisher.products.index') }}">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Product Details -->
    <div class="product-details-grid">
        <!-- Product Image Section -->
        <div class="product-image-section">
            <div class="product-main-image">
                <img src="{{ get_image_url($product->image) }}" 
                     alt="{{ $product->name }}" 
                     class="product-detail-image">
            </div>
        </div>

        <!-- Product Info Section -->
        <div class="product-info-section">
            <div class="product-header-info">
                <h2 class="product-title">{{ $product->name }}</h2>
                <div class="product-meta">
                    <span class="category-badge">
                        {{ $product->category->name ?? 'Không phân loại' }}
                    </span>
                    <span class="shop-badge">
                        <i class="fas fa-store"></i>
                        {{ $product->shopOwner->name ?? 'Unknown' }}
                    </span>
                </div>
            </div>

            <div class="product-description">
                <h3>Mô tả sản phẩm</h3>
                <p>{{ $product->description ?: 'Chưa có mô tả chi tiết' }}</p>
            </div>

            <div class="product-details-grid-info">
                <div class="detail-item">
                    <span class="detail-label">Giá sản phẩm:</span>
                    <span class="detail-value price-value">{{ $product->formatted_price }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Tỷ lệ hoa hồng:</span>
                    <span class="detail-value">
                        <span class="commission-badge">{{ $product->commission_rate ?? 0 }}%</span>
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Tình trạng:</span>
                    <span class="detail-value">
                        <span class="status-badge {{ $product->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $product->is_active ? 'Đang bán' : 'Ngừng bán' }}
                        </span>
                    </span>
                </div>
            </div>

            <!-- Affiliate Link Section -->
            <div class="affiliate-link-section">
                <h3>Link tiếp thị của bạn</h3>
                
                @if($existingLink)
                    <div class="existing-link">
                        <div class="link-info">
                            <span class="link-label">Link tiếp thị:</span>
                            <div class="link-display">
                                <input type="text" 
                                       value="{{ url('/ref/' . $existingLink->short_code) }}" 
                                       readonly 
                                       class="link-input"
                                       id="existingAffiliateLink">
                                <button type="button" 
                                        class="copy-btn" 
                                        onclick="copyToClipboard('existingAffiliateLink')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="link-info">
                            <span class="link-label">Mã rút gọn:</span>
                            <div class="link-display">
                                <input type="text" 
                                       value="{{ $existingLink->short_code }}" 
                                       readonly 
                                       class="link-input"
                                       id="shortCode">
                                <button type="button" 
                                        class="copy-btn" 
                                        onclick="copyToClipboard('shortCode')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="link-stats">
                            <div class="stat-item">
                                <span class="stat-label">Lượt click:</span>
                                <span class="stat-value">{{ $existingLink->total_clicks }}</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Chuyển đổi:</span>
                                <span class="stat-value">{{ $existingLink->total_conversions }}</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Thu nhập:</span>
                                <span class="stat-value">{{ number_format($existingLink->total_commission) }} VNĐ</span>
                            </div>
                        </div>
                        
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i>
                            Bạn đã có link tiếp thị cho sản phẩm này!
                        </div>
                    </div>
                @else
                    <div class="generate-link">
                        <p>Tạo link tiếp thị để bắt đầu kiếm hoa hồng từ sản phẩm này.</p>
                        <button type="button" 
                                class="generate-btn" 
                                onclick="generateAffiliateLink({{ $product->id }})"
                                id="generateBtn">
                            <i class="fas fa-link"></i>
                            Tạo Link Tiếp Thị
                        </button>
                    </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="product-actions">
                @if(!$existingLink)
                <button type="button" 
                        class="generate-btn large" 
                        onclick="generateAffiliateLink({{ $product->id }})"
                        id="generateBtnLarge">
                    <i class="fas fa-link"></i>
                    Tạo Link Tiếp Thị Ngay
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

@include('components.alerts')
@endsection

@push('scripts')
<script src="{{ asset('js/components/confirm-popup.js') }}"></script>
<script>
function generateAffiliateLink(productId) {
    const generateBtn = document.getElementById('generateBtn');
    const generateBtnLarge = document.getElementById('generateBtnLarge');
    
    if (generateBtn) generateBtn.disabled = true;
    if (generateBtnLarge) generateBtnLarge.disabled = true;
    
    if (generateBtn) generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
    if (generateBtnLarge) generateBtnLarge.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
    
    fetch(`/publisher/products/${productId}/affiliate-link`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showConfirmPopup({
                title: 'Thành công',
                message: data.message + '\n\nLink: ' + data.affiliate_link + '\nMã rút gọn: ' + data.short_code,
                type: 'success',
                confirmText: 'OK',
                onConfirm: () => {
                    // Reload page to show the new affiliate link
                    location.reload();
                }
            });
        } else {
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
            message: 'Có lỗi xảy ra khi tạo link tiếp thị',
            type: 'danger',
            confirmText: 'OK',
            onConfirm: () => {}
        });
    })
    .finally(() => {
        if (generateBtn) {
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="fas fa-link"></i> Tạo Link Tiếp Thị';
        }
        if (generateBtnLarge) {
            generateBtnLarge.disabled = false;
            generateBtnLarge.innerHTML = '<i class="fas fa-link"></i> Tạo Link Tiếp Thị Ngay';
        }
    });
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        
        // Show success message
        const copyBtn = element.nextElementSibling;
        const originalHTML = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="fas fa-check"></i>';
        copyBtn.style.background = '#10b981';
        
        setTimeout(() => {
            copyBtn.innerHTML = originalHTML;
            copyBtn.style.background = '';
        }, 2000);
        
    } catch (err) {
        console.error('Failed to copy: ', err);
    }
}
</script>
@endpush
