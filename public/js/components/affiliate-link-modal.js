/**
 * Affiliate Link Modal Component
 * Handles the creation and display of affiliate links
 */
class AffiliateLinkModal {
    constructor() {
        this.modal = null;
        this.currentProductId = null;
        this.init();
    }

    /**
     * Initialize the modal
     */
    init() {
        this.createModal();
        this.bindEvents();
    }

    /**
     * Create modal HTML structure
     */
    createModal() {
        const modalHTML = `
            <div id="affiliateLinkModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Chia sẻ sản phẩm</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="product-preview mb-3">
                                <img id="modalProductImage" src="" alt="Product" class="img-thumbnail" style="max-width: 100px;">
                                <span class="badge bg-success ms-2">Active</span>
                            </div>
                            
                            <div class="form-group">
                                <label for="affiliateLinkInput" class="form-label">Link tiếp thị của bạn:</label>
                                <div class="input-group">
                                    <input type="text" id="affiliateLinkInput" class="form-control" readonly>
                                    <button class="btn btn-success" type="button" id="copyLinkBtn">
                                        <i class="fas fa-copy"></i> Sao chép
                                    </button>
                                </div>
                                <div id="errorMessage" class="text-danger mt-2" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add modal to body if it doesn't exist
        if (!document.getElementById('affiliateLinkModal')) {
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }

        this.modal = new bootstrap.Modal(document.getElementById('affiliateLinkModal'));
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Copy button click
        document.addEventListener('click', (e) => {
            if (e.target.id === 'copyLinkBtn') {
                this.copyToClipboard();
            }
        });

        // Modal hidden event
        document.getElementById('affiliateLinkModal').addEventListener('hidden.bs.modal', () => {
            this.resetModal();
        });
    }

    /**
     * Show modal for a specific product
     */
    show(productId, productData = {}) {
        this.currentProductId = productId;
        
        // Update product preview
        if (productData.image) {
            document.getElementById('modalProductImage').src = productData.image;
        }
        
        // Show modal
        this.modal.show();
        
        // Create affiliate link
        this.createAffiliateLink(productId);
    }

    /**
     * Create affiliate link via AJAX
     */
    async createAffiliateLink(productId) {
        try {
            this.showLoading();
            this.hideError();

            const response = await fetch(`/publisher/products/${productId}/affiliate-link`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(data.data);
            } else {
                this.showError(data.message);
            }

        } catch (error) {
            console.error('Error creating affiliate link:', error);
            this.showError('Có lỗi xảy ra khi tạo link tiếp thị. Vui lòng thử lại.');
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Show loading state
     */
    showLoading() {
        const input = document.getElementById('affiliateLinkInput');
        input.value = 'Đang tạo link...';
        input.disabled = true;
        
        const copyBtn = document.getElementById('copyLinkBtn');
        copyBtn.disabled = true;
        copyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        const input = document.getElementById('affiliateLinkInput');
        input.disabled = false;
        
        const copyBtn = document.getElementById('copyLinkBtn');
        copyBtn.disabled = false;
        copyBtn.innerHTML = '<i class="fas fa-copy"></i> Sao chép';
    }

    /**
     * Show success state
     */
    showSuccess(data) {
        const input = document.getElementById('affiliateLinkInput');
        input.value = data.affiliate_link;
        
        // Enable copy button
        const copyBtn = document.getElementById('copyLinkBtn');
        copyBtn.disabled = false;
        
        // Show success message
        this.showMessage('Link tiếp thị đã được tạo thành công!', 'success');
    }

    /**
     * Show error state
     */
    showError(message) {
        const input = document.getElementById('affiliateLinkInput');
        input.value = '';
        
        // Disable copy button
        const copyBtn = document.getElementById('copyLinkBtn');
        copyBtn.disabled = true;
        
        // Show error message
        this.showMessage(message, 'error');
    }

    /**
     * Show message
     */
    showMessage(message, type) {
        const errorDiv = document.getElementById('errorMessage');
        errorDiv.textContent = message;
        errorDiv.className = `text-${type === 'success' ? 'success' : 'danger'} mt-2`;
        errorDiv.style.display = 'block';
    }

    /**
     * Hide error message
     */
    hideError() {
        const errorDiv = document.getElementById('errorMessage');
        errorDiv.style.display = 'none';
    }

    /**
     * Copy link to clipboard
     */
    async copyToClipboard() {
        const input = document.getElementById('affiliateLinkInput');
        
        try {
            await navigator.clipboard.writeText(input.value);
            this.showCopySuccess();
        } catch (err) {
            // Fallback for older browsers
            input.select();
            document.execCommand('copy');
            this.showCopySuccess();
        }
    }

    /**
     * Show copy success message
     */
    showCopySuccess() {
        const copyBtn = document.getElementById('copyLinkBtn');
        const originalText = copyBtn.innerHTML;
        
        copyBtn.innerHTML = '<i class="fas fa-check"></i> Đã sao chép!';
        copyBtn.className = 'btn btn-success';
        
        setTimeout(() => {
            copyBtn.innerHTML = originalText;
            copyBtn.className = 'btn btn-success';
        }, 2000);
    }

    /**
     * Reset modal to initial state
     */
    resetModal() {
        const input = document.getElementById('affiliateLinkInput');
        input.value = '';
        input.disabled = false;
        
        const copyBtn = document.getElementById('copyLinkBtn');
        copyBtn.disabled = true;
        copyBtn.innerHTML = '<i class="fas fa-copy"></i> Sao chép';
        
        this.hideError();
        this.currentProductId = null;
    }

    /**
     * Hide modal
     */
    hide() {
        this.modal.hide();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.affiliateLinkModal = new AffiliateLinkModal();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AffiliateLinkModal;
}
