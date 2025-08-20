@extends('components.dashboard.layout')

@section('title', 'Tạo Affiliate Link')

@section('content')
<div class="affiliate-links-container">
    <!-- Header Section -->
    <div class="affiliate-header">
        <div class="affiliate-header-left">
            <h1 class="affiliate-title">Tạo Affiliate Link</h1>
            <p class="affiliate-description">Tạo affiliate link mới</p>
        </div>
        <div class="affiliate-header-right">
            <a href="{{ route('admin.affiliate-links.index') }}" class="affiliate-btn affiliate-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Quay lại</span>
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <div class="affiliate-form-card">
        <div class="form-header">
            <h3 class="form-title">Thông tin Affiliate Link</h3>
            <p class="form-subtitle">Điền thông tin để tạo affiliate link mới</p>
        </div>
        
        <div class="form-body">
            <form method="POST" action="{{ route('admin.affiliate-links.store') }}" class="affiliate-form">
                @csrf
                
                <div class="form-grid">
                                            <!-- Publisher Selection -->
                        <div class="form-group">
                            <label for="publisher_id" class="form-label">
                                Publisher <span class="required">*</span>
                            </label>
                            <select name="publisher_id" id="publisher_id" class="form-select" required>
                                <option value="">Chọn Publisher</option>
                                @foreach($publishers ?? [] as $publisher)
                                    @if($publisher->role === 'publisher')
                                        <option value="{{ $publisher->id }}" {{ old('publisher_id') == $publisher->id ? 'selected' : '' }}>
                                            {{ $publisher->name }} ({{ $publisher->email }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('publisher_id')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                    <!-- Product Selection -->
                    <div class="form-group">
                        <label for="product_id" class="form-label">
                            Sản phẩm <span class="required">*</span>
                        </label>
                        <select name="product_id" id="product_id" class="form-select" required>
                            <option value="">Chọn sản phẩm</option>
                            @foreach($products ?? [] as $product)
                                <option value="{{ $product->id }}" 
                                        data-commission="{{ $product->commission_rate ?? 0 }}"
                                        data-affiliate-link="{{ $product->affiliate_link ?? '' }}"
                                        {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} - {{ $product->category->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Campaign Selection -->
                    <div class="form-group">
                        <label for="campaign_id" class="form-label">Campaign</label>
                        <select name="campaign_id" id="campaign_id" class="form-select">
                            <option value="">Không có campaign</option>
                            @foreach($campaigns ?? [] as $campaign)
                                <option value="{{ $campaign->id }}" {{ old('campaign_id') == $campaign->id ? 'selected' : '' }}>
                                    {{ $campaign->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('campaign_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Commission Rate -->
                    <div class="form-group">
                        <label for="commission_rate" class="form-label">
                            Tỷ lệ hoa hồng (%) <span class="required">*</span>
                        </label>
                        <input type="number" name="commission_rate" id="commission_rate" 
                               class="form-input" min="0" max="100" step="0.01" 
                               value="{{ old('commission_rate', 10) }}" required>
                        <div id="product-commission-info" class="form-help" style="display: none;">
                            <i class="fas fa-info-circle"></i>
                            <span id="product-commission-text">Commission rate của sản phẩm: <strong id="product-commission-value">0%</strong></span>
                        </div>
                        @error('commission_rate')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Original URL and Status -->
                    <div class="form-group">
                        <label for="original_url" class="form-label">
                            URL gốc <span class="required">*</span>
                        </label>
                        <input type="url" name="original_url" id="original_url" 
                               class="form-input" value="{{ old('original_url') }}" 
                               placeholder="https://example.com/product" required>
                        @error('original_url')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">
                            Trạng thái <span class="required">*</span>
                        </label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Vô hiệu hóa</option>
                        </select>
                        @error('status')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tracking Code Preview -->
                    <div class="form-group form-group-full">
                        <label class="form-label">Tracking Code Preview</label>
                        <div class="tracking-preview">
                            <div class="code-preview-item">
                                <label class="code-label">Tracking Code:</label>
                                <code class="tracking-code-display" id="trackingPreview">Chọn Publisher và Product để xem preview</code>
                                <button type="button" class="copy-btn" onclick="copyToClipboard(document.getElementById('trackingPreview').textContent)" title="Copy tracking code">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <div class="code-preview-item">
                                <label class="code-label">Short Code:</label>
                                <code class="short-code-display" id="shortCodePreview">Chọn Publisher và Product để xem preview</code>
                                <button type="button" class="copy-btn" onclick="copyToClipboard(document.getElementById('shortCodePreview').textContent)" title="Copy short code">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-help">Tracking code và Short code sẽ được tạo tự động khi lưu</small>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="affiliate-btn affiliate-btn-primary">
                        <i class="fas fa-save"></i>
                        <span>Tạo Affiliate Link</span>
                    </button>
                    <a href="{{ route('admin.affiliate-links.index') }}" class="affiliate-btn affiliate-btn-secondary">
                        <i class="fas fa-times"></i>
                        <span>Hủy</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('publisher_id').addEventListener('change', updatePreview);
document.getElementById('product_id').addEventListener('change', updatePreviewAndCommission);

// Khởi tạo commission rate và URL khi trang được load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('product_id').value) {
        updateCommissionRate();
        updateOriginalUrl();
    }
});

function updatePreview() {
    const publisherSelect = document.getElementById('publisher_id');
    const productSelect = document.getElementById('product_id');
    const trackingPreview = document.getElementById('trackingPreview');
    const shortCodePreview = document.getElementById('shortCodePreview');
    
    if (publisherSelect.value && productSelect.value) {
        const publisher = publisherSelect.options[publisherSelect.selectedIndex].text.split(' (')[0];
        const product = productSelect.options[productSelect.selectedIndex].text.split(' - ')[0];
        
        // Generate tracking code preview
        trackingPreview.textContent = `AFF_${publisher.substring(0, 3).toUpperCase()}_${product.substring(0, 3).toUpperCase()}_${Date.now().toString(36)}`;
        
        // Generate short code preview (6 random characters)
        shortCodePreview.textContent = Math.random().toString(36).substring(2, 8).toUpperCase();
    } else {
        trackingPreview.textContent = 'Chọn Publisher và Product để xem preview';
        shortCodePreview.textContent = 'Chọn Publisher và Product để xem preview';
    }
}

function updatePreviewAndCommission() {
    updatePreview();
    updateCommissionRate();
    updateOriginalUrl();
}

function updateCommissionRate() {
    const productSelect = document.getElementById('product_id');
    const commissionInput = document.getElementById('commission_rate');
    const productCommissionInfo = document.getElementById('product-commission-info');
    const productCommissionValue = document.getElementById('product-commission-value');
    
    if (productSelect.value) {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const productCommission = parseFloat(selectedOption.getAttribute('data-commission')) || 0;
        
        // Tự động điền commission rate của sản phẩm
        commissionInput.value = productCommission;
        
        // Hiển thị thông tin commission rate của sản phẩm
        productCommissionValue.textContent = productCommission + '%';
        productCommissionInfo.style.display = 'block';
        
        // Thêm class để styling
        productCommissionInfo.className = 'form-help commission-info';
    } else {
        // Ẩn thông tin khi không có sản phẩm nào được chọn
        productCommissionInfo.style.display = 'none';
    }
}

function updateOriginalUrl() {
    const productSelect = document.getElementById('product_id');
    const originalUrlInput = document.getElementById('original_url');
    
    if (productSelect.value) {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const affiliateLink = selectedOption.getAttribute('data-affiliate-link');
        
        if (affiliateLink) {
            originalUrlInput.value = affiliateLink;
        }
    }
}

function copyToClipboard(text) {
    if (text === 'Chọn Publisher và Product để xem preview') {
        alert('Vui lòng chọn Publisher và Product trước');
        return;
    }
    
    navigator.clipboard.writeText(text).then(function() {
        const btn = event.target.closest('button');
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.add('copy-success');
        
        setTimeout(() => {
            btn.innerHTML = originalIcon;
            btn.classList.remove('copy-success');
        }, 2000);
    });
}
</script>
@endsection
