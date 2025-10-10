@extends('publisher.layouts.app')

@section('title', 'Khám phá Chiến dịch')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/publisher/campaigns.css') }}">
@endpush

@section('content')
<div class="product-management-content">
    <div class="product-management-header">
        <div class="product-management-title">
            <h2>Khám phá Chiến dịch</h2>
            <p>Tham gia các chiến dịch tiếp thị và kiếm hoa hồng hấp dẫn</p>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="product-filters-card">
        <div class="product-filters-header">
            <h5 class="product-filters-title">Bộ lọc & Tìm kiếm</h5>
        </div>
        <div class="product-filters-body">
            <form method="GET" action="{{ route('publisher.campaigns.index') }}" class="product-filters-form">
                <div class="product-filters-row">
                    
                    <!-- Search -->
                    <div class="product-filter-group">
                        <label class="product-filter-label">Tìm kiếm</label>
                        <input type="text" 
                               name="search" 
                               class="product-filter-input" 
                               placeholder="Tên chiến dịch..."
                               value="{{ request('search') }}">
                    </div>
                    
                    <!-- Commission Rate -->
                    <div class="product-filter-group">
                        <label class="product-filter-label">Hoa hồng tối thiểu (%)</label>
                        <input type="number" 
                               name="min_commission" 
                               class="product-filter-input" 
                               placeholder="0"
                               min="0"
                               step="0.1"
                               value="{{ request('min_commission') }}">
                    </div>
                    
                    <!-- Budget -->
                    <div class="product-filter-group">
                        <label class="product-filter-label">Ngân sách tối thiểu (VNĐ)</label>
                        <input type="number" 
                               name="min_budget" 
                               class="product-filter-input" 
                               placeholder="0"
                               min="0"
                               step="1000"
                               value="{{ request('min_budget') }}">
                    </div>
                    
                    <!-- Actions -->
                    <div class="product-filter-actions">
                        <button type="submit" class="product-filter-btn product-filter-btn-primary">
                            <i class="fas fa-search"></i>
                            Lọc
                        </button>
                        <a href="{{ route('publisher.campaigns.index') }}" class="product-filter-btn product-filter-btn-secondary">
                            <i class="fas fa-redo"></i>
                            Reset
                        </a>
                    </div>
                    
                </div>
            </form>
        </div>
    </div>

    <!-- Campaign Results -->
    <div class="product-management-card">
        <div class="product-management-card-header">
            <h5 class="product-management-card-title">
                <i class="fas fa-bullhorn text-primary"></i>
                Các chiến dịch đang hoạt động
            </h5>
            <span class="product-count-badge">{{ $campaigns->total() }} chiến dịch</span>
        </div>

        <div class="product-management-card-body">
            @if($campaigns->count() > 0)
                <div class="campaigns-grid">
                    @foreach($campaigns as $campaign)
                        <div class="campaign-card">
                            <div class="campaign-icon-container">
                                <span class="campaign-badge">
                                    <i class="fas fa-fire"></i> HOT
                                </span>
                            </div>
                            
                            <div class="campaign-content">
                                <h3 class="campaign-name">{{ $campaign->name }}</h3>
                                
                                <div class="campaign-dates">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ $campaign->start_date->format('d/m/Y') }} - {{ $campaign->end_date->format('d/m/Y') }}
                                </div>
                                
                                <p class="campaign-description">
                                    {{ $campaign->description ?? 'Chiến dịch tiếp thị hấp dẫn với nhiều ưu đãi.' }}
                                </p>
                                
                                <div class="campaign-info-row">
                                    <div>
                                        <div class="campaign-info-label">Hoa hồng</div>
                                        <div class="campaign-commission">{{ number_format($campaign->commission_rate ?? 0, 1) }}%</div>
                                    </div>
                                    <div>
                                        <div class="campaign-info-label">CPC</div>
                                        <div class="campaign-info-value">{{ number_format($campaign->cost_per_click ?? 100, 0) }} VNĐ</div>
                                    </div>
                                </div>
                                
                                <div class="campaign-info-row" style="margin-bottom: 15px;">
                                    <div>
                                        <div class="campaign-info-label">Ngân sách</div>
                                        <div class="campaign-info-value">{{ number_format($campaign->budget ?? 0, 0, ',', '.') }} VNĐ</div>
                                    </div>
                                    <div>
                                        <div class="campaign-info-label">Mục tiêu</div>
                                        <div class="campaign-info-value">{{ number_format($campaign->target_conversions ?? 0) }} CV</div>
                                    </div>
                                </div>
                                
                                <button class="campaign-action-btn" onclick="openCampaignModal({{ $campaign->id }}, '{{ $campaign->name }}', {{ $campaign->commission_rate ?? 0 }}, {{ $campaign->cost_per_click ?? 100 }})">
                                    <i class="fas fa-link"></i>
                                    Lấy link tiếp thị
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                @if(request()->hasAny(['search', 'min_commission', 'min_budget']))
                    <!-- No search results -->
                    <div class="no-results">
                        <div class="no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="no-results-title">Không tìm thấy kết quả</h3>
                        <p class="no-results-description">
                            Không có chiến dịch nào phù hợp với tiêu chí tìm kiếm của bạn.
                        </p>
                    </div>
                @else
                    <!-- No campaigns available -->
                    <div class="no-results">
                        <div class="no-results-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h3 class="no-results-title">Chưa có chiến dịch</h3>
                        <p class="no-results-description">
                            Hiện tại chưa có chiến dịch nào đang hoạt động. Vui lòng quay lại sau!
                        </p>
                    </div>
                @endif
            @endif
        </div>

        @if($campaigns->hasPages())
            <div class="product-management-card-footer">
                <div class="product-pagination">
                    {{ $campaigns->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Campaign Link Modal -->
<div id="campaignModal" class="campaign-modal">
    <div class="campaign-modal-content">
        <div class="campaign-modal-header">
            <h3><i class="fas fa-link"></i> Tạo Link Tiếp thị</h3>
            <span class="campaign-modal-close">&times;</span>
        </div>
        <div class="campaign-modal-body">
            <!-- Form Section -->
            <div id="createLinkSection">
                <form id="createCampaignLinkForm">
                    <!-- Destination URL -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-globe"></i> Link gốc (Destination URL) <span class="required">*</span>
                        </label>
                        <input type="url" 
                               id="destinationUrl" 
                               class="form-input" 
                               placeholder="https://example.com/product-page"
                               required>
                        <small class="form-help">
                            Nhập URL của trang bạn muốn quảng bá (ví dụ: link Facebook, TikTok, YouTube,...)
                        </small>
                    </div>
                    
                    <button type="button" class="campaign-action-btn" onclick="createCampaignLink()" id="createLinkBtn">
                        <i class="fas fa-magic"></i> Tạo Link Rút Gọn
                    </button>
                </form>
            </div>
            
            <!-- Result Section -->
            <div id="linkSection" class="link-result-section" style="display: none;">
                <div class="link-result-box">
                    <div class="link-result-label">
                        <i class="fas fa-check-circle"></i> Link rút gọn của bạn
                    </div>
                    <div class="link-result-value" id="modalAffiliateLink"></div>
                    <button class="btn-copy-link" onclick="copyCampaignLink()">
                        <i class="fas fa-copy"></i> Sao chép link
                    </button>
                </div>
                
                <div class="success-message" id="copySuccessMessage">
                    <i class="fas fa-check-circle"></i> Đã sao chép link thành công!
                </div>
                
                <button class="btn-new-link" onclick="resetForm()">
                    <i class="fas fa-plus"></i> Tạo link mới
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentCampaignId = null;
let currentAffiliateLink = null;

function openCampaignModal(campaignId, campaignName, commissionRate, costPerClick) {
    currentCampaignId = campaignId;
    
    // Reset form
    resetForm();
    
    // Show modal
    document.getElementById('campaignModal').style.display = 'block';
}

function closeCampaignModal() {
    document.getElementById('campaignModal').style.display = 'none';
    currentCampaignId = null;
    currentAffiliateLink = null;
    resetForm();
}

function resetForm() {
    // Clear form inputs
    document.getElementById('destinationUrl').value = '';
    
    // Show form, hide result
    document.getElementById('linkSection').style.display = 'none';
    document.getElementById('createLinkSection').style.display = 'block';
    document.getElementById('copySuccessMessage').classList.remove('show');
}

// Close modal when clicking the X
document.querySelector('.campaign-modal-close').onclick = closeCampaignModal;

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('campaignModal');
    if (event.target == modal) {
        closeCampaignModal();
    }
}

async function createCampaignLink() {
    const destinationUrl = document.getElementById('destinationUrl').value.trim();
    
    // Validate destination URL
    if (!destinationUrl) {
        if (typeof window.showAlert === 'function') {
            window.showAlert('Vui lòng nhập link gốc (Destination URL)', 'error');
        } else {
            alert('Vui lòng nhập link gốc (Destination URL)');
        }
        return;
    }
    
    // Validate URL format
    try {
        new URL(destinationUrl);
    } catch (e) {
        if (typeof window.showAlert === 'function') {
            window.showAlert('Link gốc không đúng định dạng. Vui lòng nhập URL hợp lệ (ví dụ: https://example.com)', 'error');
        } else {
            alert('Link gốc không đúng định dạng. Vui lòng nhập URL hợp lệ (ví dụ: https://example.com)');
        }
        return;
    }
    
    const btn = document.getElementById('createLinkBtn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
    
    try {
        const response = await fetch(`/publisher/campaigns/${currentCampaignId}/affiliate-link`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                destination_url: destinationUrl
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentAffiliateLink = data.affiliate_link;
            document.getElementById('modalAffiliateLink').textContent = data.affiliate_link;
            document.getElementById('linkSection').style.display = 'block';
            document.getElementById('createLinkSection').style.display = 'none';
            
            // Show success alert
            if (typeof window.showAlert === 'function') {
                window.showAlert('Tạo link rút gọn thành công!', 'success');
            }
        } else {
            if (typeof window.showAlert === 'function') {
                window.showAlert(data.message || 'Có lỗi xảy ra khi tạo link', 'error');
            } else {
                alert(data.message || 'Có lỗi xảy ra khi tạo link');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        if (typeof window.showAlert === 'function') {
            window.showAlert('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
        } else {
            alert('Có lỗi xảy ra. Vui lòng thử lại!');
        }
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function copyCampaignLink() {
    const linkText = document.getElementById('modalAffiliateLink').textContent;
    
    navigator.clipboard.writeText(linkText).then(() => {
        const successMsg = document.getElementById('copySuccessMessage');
        successMsg.classList.add('show');
        
        setTimeout(() => {
            successMsg.classList.remove('show');
        }, 3000);
    }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Không thể sao chép link. Vui lòng thử lại!');
    });
}
</script>
@endpush
