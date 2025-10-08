@extends('publisher.layouts.app')

@section('title', 'Sửa Affiliate Link - Publisher Dashboard')

@section('content')
<div class="publisher-content">
    <!-- Header -->
    <div class="content-header">
        <div class="header-left">
            <h1>Sửa Affiliate Link</h1>
            <p>Chỉnh sửa thông tin affiliate link</p>
        </div>
        <div class="header-right">
            <a href="{{ route('publisher.affiliate-links.show', $affiliateLink) }}" class="btn btn-secondary">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
            <a href="{{ route('publisher.affiliate-links.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-section">
        <form method="POST" action="{{ route('publisher.affiliate-links.update', $affiliateLink) }}" class="affiliate-form">
            @csrf
            @method('PUT')
            
            <!-- Product Selection -->
            <div class="form-group">
                <label for="product_id" class="form-label required">Chọn sản phẩm</label>
                <select name="product_id" id="product_id" class="form-select" required>
                    <option value="">Chọn sản phẩm để tạo link...</option>
                    @foreach($products ?? [] as $product)
                        <option value="{{ $product->id }}" 
                                data-commission="{{ $product->commission_rate ?? 15 }}"
                                data-price="{{ $product->price }}"
                                data-link="{{ $product->affiliate_link ?? '#' }}"
                                {{ old('product_id', $affiliateLink->product_id) == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} - {{ number_format($product->price) }} VNĐ
                        </option>
                    @endforeach
                </select>
                @error('product_id')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Campaign Selection -->
            <div class="form-group">
                <label for="campaign_id" class="form-label">Campaign (Tùy chọn)</label>
                <select name="campaign_id" id="campaign_id" class="form-select">
                    <option value="">Không thuộc campaign nào</option>
                    @foreach($campaigns ?? [] as $campaign)
                        <option value="{{ $campaign->id }}" {{ old('campaign_id', $affiliateLink->campaign_id) == $campaign->id ? 'selected' : '' }}>
                            {{ $campaign->name }}
                        </option>
                    @endforeach
                </select>
                @error('campaign_id')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Original URL -->
            <div class="form-group">
                <label for="original_url" class="form-label required">URL gốc</label>
                <input type="url" name="original_url" id="original_url" class="form-control" 
                       placeholder="https://example.com/product" 
                       value="{{ old('original_url', $affiliateLink->original_url) }}" required>
                <small class="form-help">URL sẽ được redirect đến khi người dùng click vào affiliate link</small>
                @error('original_url')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Commission Rate -->
            <div class="form-group">
                <label for="commission_rate" class="form-label required">Tỷ lệ hoa hồng (%)</label>
                <input type="number" name="commission_rate" id="commission_rate" class="form-control" 
                       min="0" max="100" step="0.01" 
                       value="{{ old('commission_rate', $affiliateLink->commission_rate) }}" required>
                <small class="form-help">Tỷ lệ hoa hồng bạn sẽ nhận được từ mỗi đơn hàng thành công</small>
                @error('commission_rate')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Current Links Info -->
            <div class="info-section">
                <h3>Thông tin Links hiện tại</h3>
                <div class="current-links">
                    <div class="link-item">
                        <label>Tracking Code:</label>
                        <code>{{ $affiliateLink->tracking_code }}</code>
                        <small class="form-help">Tracking code không thể thay đổi</small>
                    </div>
                    <div class="link-item">
                        <label>Short URL:</label>
                        <div class="link-group">
                            <input type="text" value="{{ route('tracking.short', $affiliateLink->short_code) }}" readonly class="form-control">
                            <button type="button" class="btn btn-sm btn-outline-primary copy-btn" onclick="copyToClipboard('{{ route('tracking.short', $affiliateLink->short_code) }}')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Cập nhật Affiliate Link
                </button>
                <a href="{{ route('publisher.affiliate-links.show', $affiliateLink) }}" class="btn btn-light">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const commissionInput = document.getElementById('commission_rate');
    const originalUrlInput = document.getElementById('original_url');

    // Update commission rate and URL when product changes
    productSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            // Update commission rate
            const commission = selectedOption.getAttribute('data-commission') || '15.00';
            commissionInput.value = commission;
            
            // Update original URL
            const productLink = selectedOption.getAttribute('data-link');
            if (productLink && productLink !== '#') {
                originalUrlInput.value = productLink;
            }
        }
    });
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Đã copy vào clipboard!', 'success');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('Đã copy vào clipboard!', 'success');
    });
}
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/publisher/affiliate-form.css') }}">
@endpush
