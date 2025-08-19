@extends('components.dashboard.layout')

@section('title', 'Tạo Affiliate Link')

@section('content')
<div class="affiliate-links-container">
    <!-- Header Section -->
    <div class="affiliate-header">
        <div class="affiliate-header-left">
            <h1 class="affiliate-title">Tạo Affiliate Link</h1>
            <nav class="affiliate-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Admin</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('admin.affiliate-links.index') }}">Quản lý Affiliate</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Tạo Affiliate Link</span>
            </nav>
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
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
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
                            <code class="tracking-code-display" id="trackingPreview">Chọn Publisher và Product để xem preview</code>
                            <button type="button" class="copy-btn" onclick="copyToClipboard(document.getElementById('trackingPreview').textContent)" title="Copy tracking code">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <small class="form-help">Tracking code sẽ được tạo tự động khi lưu</small>
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
document.getElementById('product_id').addEventListener('change', updatePreview);

function updatePreview() {
    const publisherSelect = document.getElementById('publisher_id');
    const productSelect = document.getElementById('product_id');
    const preview = document.getElementById('trackingPreview');
    
    if (publisherSelect.value && productSelect.value) {
        const publisher = publisherSelect.options[publisherSelect.selectedIndex].text.split(' (')[0];
        const product = productSelect.options[productSelect.selectedIndex].text.split(' - ')[0];
        
        preview.textContent = `AFF_${publisher.substring(0, 3).toUpperCase()}_${product.substring(0, 3).toUpperCase()}_${Date.now().toString(36)}`;
    } else {
        preview.textContent = 'Chọn Publisher và Product để xem preview';
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
