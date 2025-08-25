@extends('publisher.layouts.app')

@section('title', 'Tạo Affiliate Link - Publisher Dashboard')

@section('content')
<div class="publisher-content">
    <!-- Header -->
    <div class="content-header">
        <div class="header-left">
            <h1>Tạo Affiliate Link</h1>
        </div>
        <div class="header-right">
            <a href="{{ route('publisher.affiliate-links.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="form-section">
        <form method="POST" action="{{ route('publisher.affiliate-links.store') }}" class="affiliate-form">
            @csrf
            
            <!-- Original URL -->
            <div class="form-group">
                <label for="original_url" class="form-label required">URL Landing Page</label>
                <input type="url" name="original_url" id="original_url" class="form-control" 
                       placeholder="https://example.com/landing-page" 
                       value="{{ old('original_url') }}" required>
                <small class="form-help">Nhập URL của trang đích mà bạn muốn tiếp thị. Khách hàng sẽ được chuyển đến trang này khi click vào affiliate link.</small>
                @error('original_url')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Campaign Selection -->
            <div class="form-group">
                <label for="campaign_id" class="form-label required">Chọn Campaign</label>
                <select name="campaign_id" id="campaign_id" class="form-select" required>
                    <option value="">Chọn campaign...</option>
                    @foreach($campaigns ?? [] as $campaign)
                        <option value="{{ $campaign->id }}" 
                                data-commission="{{ $campaign->commission_rate ?? 15 }}"
                                data-description="{{ $campaign->description ?? '' }}"
                                {{ old('campaign_id', $selected_campaign_id ?? '') == $campaign->id ? 'selected' : '' }}>
                            {{ $campaign->name }} - {{ $campaign->commission_rate ?? 15 }}% commission
                        </option>
                    @endforeach
                </select>
                <small class="form-help">Campaign sẽ quyết định tỷ lệ hoa hồng bạn nhận được từ các conversion</small>
                @error('campaign_id')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <!-- Commission Rate Display -->
            <div class="form-group">
                <label class="form-label">Tỷ lệ hoa hồng</label>
                <div class="commission-display">
                    <span id="commission-rate-display">Chọn campaign để xem tỷ lệ hoa hồng</span>
                </div>
                <small class="form-help">Tỷ lệ hoa hồng được thiết lập bởi campaign</small>
            </div>
            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Tạo Affiliate Link
                </button>
                <button type="button" class="btn btn-light" onclick="confirmCancel()">
                    <i class="fas fa-times"></i>
                    Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const campaignSelect = document.getElementById('campaign_id');
    const commissionDisplay = document.getElementById('commission-rate-display');

    // Update commission rate display when campaign changes
    campaignSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            // Update commission rate display
            const commission = selectedOption.getAttribute('data-commission') || '15';
            commissionDisplay.textContent = `${commission}%`;
            commissionDisplay.className = 'commission-rate-active';
            
            // Show description if available
            const description = selectedOption.getAttribute('data-description');
            if (description) {
                commissionDisplay.textContent += ` - ${description}`;
            }
        } else {
            commissionDisplay.textContent = 'Chọn campaign để xem tỷ lệ hoa hồng';
            commissionDisplay.className = 'commission-rate-inactive';
        }
    });

    // Trigger update if campaign is pre-selected
    if (campaignSelect.value) {
        campaignSelect.dispatchEvent(new Event('change'));
    }
});

// Confirm before canceling form if user has entered data
function confirmCancel() {
    const originalUrl = document.getElementById('original_url').value;
    const campaignId = document.getElementById('campaign_id').value;
    
    // Check if user has entered any data
    if (originalUrl.trim() || campaignId) {
        showConfirmPopup({
            title: 'Hủy tạo Affiliate Link',
            message: 'Bạn có chắc chắn muốn hủy? Tất cả dữ liệu đã nhập sẽ bị mất.',
            type: 'warning',
            confirmText: 'Có, hủy bỏ',
            cancelText: 'Tiếp tục chỉnh sửa',
            onConfirm: function() {
                window.location.href = '{{ route("publisher.affiliate-links.index") }}';
            }
        });
    } else {
        // If no data entered, redirect immediately
        window.location.href = '{{ route("publisher.affiliate-links.index") }}';
    }
}
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/publisher/affiliate-form.css') }}">
@endpush
