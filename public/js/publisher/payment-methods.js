// Payment Methods Management JavaScript
class PaymentMethodManager {
    constructor() {
        this.init();
    }

    init() {
        this.initEventListeners();
        this.initPaymentMethodForm();
        // Không cần load từ API vì đã có server-side rendering
    }

    initEventListeners() {
        // Add payment method button
        const addBtn = document.querySelector('[onclick="openPaymentMethodModal()"]');
        if (addBtn) {
            addBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.openPaymentMethodModal();
            });
        }

        // Refresh button
        const refreshBtn = document.querySelector('[onclick="refreshList()"]');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.reload();
            });
        }
    }

    async loadPaymentMethods() {
        try {
            const response = await fetch('/publisher/payment-methods/api/list');
            const data = await response.json();

            if (data.success) {
                this.updatePaymentMethodsGrid(data.payment_methods || []);
            } else {
                showError(data.message || 'Không thể tải danh sách tài khoản thanh toán');
            }
        } catch (error) {
            console.error('Error loading payment methods:', error);
            showError('Có lỗi xảy ra khi tải danh sách tài khoản thanh toán');
        }
    }

    updatePaymentMethodsGrid(paymentMethods) {
        const grid = document.getElementById('payment-methods-grid');
        if (!grid) {
            console.error('Payment methods grid not found!');
            return;
        }

        if (paymentMethods.length === 0) {
            grid.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-credit-card"></i>
                    <h3>Chưa có tài khoản thanh toán</h3>
                    <p>Thêm tài khoản ngân hàng để bắt đầu rút tiền</p>
                    <button class="btn btn-primary" onclick="paymentMethodManager.openPaymentMethodModal()">
                        <i class="fas fa-plus"></i>
                        Thêm tài khoản
                    </button>
                </div>
            `;
            return;
        }

        grid.innerHTML = paymentMethods.map(method => `
            <div class="payment-method-card ${method.is_default ? 'default' : ''}" 
                 data-method-id="${method.id}">
                <div class="card-header">
                    <div class="method-icon">
                        <i class="${method.icon}"></i>
                    </div>
                    <div class="method-info">
                        <h4 class="method-title">${method.type_label}</h4>
                        <p class="method-subtitle">${method.account_name || 'N/A'}</p>
                    </div>
                    ${method.is_default ? `
                        <div class="default-badge">
                            <i class="fas fa-star"></i>
                            Mặc định
                        </div>
                    ` : ''}
                </div>

                <div class="card-body">
                    <div class="method-details">
                        <div class="detail-row">
                            <span class="detail-label">Số tài khoản:</span>
                            <span class="detail-value">${method.masked_account_number}</span>
                        </div>
                        ${method.bank_name ? `
                            <div class="detail-row">
                                <span class="detail-label">Ngân hàng:</span>
                                <span class="detail-value">${method.bank_name}</span>
                            </div>
                        ` : ''}
                        ${method.branch_name ? `
                            <div class="detail-row">
                                <span class="detail-label">Chi nhánh:</span>
                                <span class="detail-value">${method.branch_name}</span>
                            </div>
                        ` : ''}
                        <div class="detail-row">
                            <span class="detail-label">Phí rút tiền:</span>
                            <span class="detail-value">${(method.fee_rate * 100).toFixed(1)}%</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Trạng thái:</span>
                            <span class="detail-value">
                                <span class="status-badge ${method.is_verified ? 'verified' : 'pending'}">
                                    ${method.is_verified ? 'Đã xác minh' : 'Chờ xác minh'}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-outline-primary" 
                                onclick="paymentMethodManager.editPaymentMethod(${method.id})"
                                title="Chỉnh sửa">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${!method.is_default ? `
                            <button class="btn btn-sm btn-outline-success" 
                                    onclick="paymentMethodManager.setAsDefault(${method.id})"
                                    title="Đặt làm mặc định">
                                <i class="fas fa-star"></i>
                            </button>
                        ` : ''}
                        <button class="btn btn-sm btn-outline-danger" 
                                onclick="paymentMethodManager.deletePaymentMethod(${method.id})"
                                title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    openPaymentMethodModal() {
        // Get existing modal from view
        const modal = document.getElementById('paymentMethodModal');
        if (!modal) {
            console.error('Payment method modal not found!');
            return;
        }

        // Reset form
        this.resetPaymentMethodForm();

        // Show modal using Bootstrap
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }


    initPaymentMethodForm() {
        // Luôn hiển thị các trường ngân hàng vì chỉ hỗ trợ bank_transfer
        const bankNameField = document.getElementById('bankNameField');
        const bankDetailsFields = document.getElementById('bankDetailsFields');
        
        if (bankNameField) {
            bankNameField.style.display = 'block';
        }
        if (bankDetailsFields) {
            bankDetailsFields.style.display = 'block';
        }
    }


    resetPaymentMethodForm() {
        const form = document.getElementById('paymentMethodForm');
        if (form) {
            form.reset();
            // Reset modal title
            document.getElementById('modalTitle').textContent = 'Thêm tài khoản thanh toán';
            // Luôn hiển thị bank fields vì chỉ hỗ trợ bank_transfer
            document.getElementById('bankNameField').style.display = 'block';
            document.getElementById('bankDetailsFields').style.display = 'block';
            // Hide preview
            document.getElementById('paymentMethodPreview').style.display = 'none';
        }
    }

    // Form sẽ submit tự động khi click nút submit

    async editPaymentMethod(id) {
        showInfo('Tính năng chỉnh sửa đang được phát triển');
    }

    async setAsDefault(id) {
        if (!confirm('Bạn có chắc chắn muốn đặt tài khoản này làm mặc định?')) {
            return;
        }

        try {
            const response = await fetch(`/publisher/payment-methods/${id}/set-default`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();

            if (result.success) {
                showSuccess(result.message);
                // Reload page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showError(result.message || 'Có lỗi xảy ra khi đặt tài khoản mặc định');
            }
        } catch (error) {
            console.error('Error setting default payment method:', error);
            showError('Có lỗi xảy ra khi đặt tài khoản mặc định');
        }
    }

    async deletePaymentMethod(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa tài khoản thanh toán này?')) {
            return;
        }

        try {
            const response = await fetch(`/publisher/payment-methods/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const result = await response.json();

            if (result.success) {
                showSuccess(result.message);
                // Reload page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showError(result.message || 'Có lỗi xảy ra khi xóa tài khoản thanh toán');
            }
        } catch (error) {
            console.error('Error deleting payment method:', error);
            showError('Có lỗi xảy ra khi xóa tài khoản thanh toán');
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.paymentMethodManager = new PaymentMethodManager();
});

// Global functions for onclick handlers
function openPaymentMethodModal() {
    if (window.paymentMethodManager) {
        window.paymentMethodManager.openPaymentMethodModal();
    }
}

function refreshList() {
    window.location.reload();
}

// Function savePaymentMethod đã được thay thế bằng form submit tự động

// Custom Dropdown with Search functionality
class CustomDropdown {
    constructor(dropdownId) {
        this.dropdown = document.getElementById(dropdownId);
        if (!this.dropdown) return;
        
        this.trigger = this.dropdown.querySelector('.dropdown-trigger');
        this.search = this.dropdown.querySelector('.dropdown-search');
        this.panel = this.dropdown.querySelector('.dropdown-panel');
        this.items = this.dropdown.querySelector('.dropdown-items');
        this.hiddenInput = this.dropdown.querySelector('input[type="hidden"]');
        this.arrow = this.dropdown.querySelector('.dropdown-arrow');
        
        this.allItems = Array.from(this.items.querySelectorAll('.dropdown-item'));
        this.filteredItems = [...this.allItems];
        this.selectedIndex = -1;
        this.isOpen = false;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
    }
    
    bindEvents() {
        // Click on trigger to toggle dropdown
        this.trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggle();
        });
        
        // Search input
        this.search.addEventListener('input', (e) => {
            this.filterItems(e.target.value);
        });
        
        // Keyboard navigation
        this.search.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });
        
        // Click on items
        this.items.addEventListener('click', (e) => {
            const item = e.target.closest('.dropdown-item');
            if (item) {
                this.selectItem(item);
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.dropdown.contains(e.target)) {
                this.close();
            }
        });
        
        // Prevent form submission on Enter in search
        this.search.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    }
    
    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }
    
    open() {
        this.isOpen = true;
        this.dropdown.classList.add('active');
        this.search.focus();
        this.selectedIndex = -1;
        this.updateHighlight();
    }
    
    close() {
        this.isOpen = false;
        this.dropdown.classList.remove('active');
        this.selectedIndex = -1;
        this.updateHighlight();
    }
    
    filterItems(searchTerm) {
        const term = searchTerm.toLowerCase().trim();
        
        this.filteredItems = this.allItems.filter(item => {
            const text = item.textContent.toLowerCase();
            const match = text.includes(term);
            item.style.display = match ? 'block' : 'none';
            return match;
        });
        
        // Show no results message if needed
        this.showNoResults(this.filteredItems.length === 0 && term !== '');
        
        // Reset selection
        this.selectedIndex = -1;
        this.updateHighlight();
    }
    
    showNoResults(show) {
        let noResultsEl = this.items.querySelector('.dropdown-no-results');
        
        if (show) {
            if (!noResultsEl) {
                noResultsEl = document.createElement('div');
                noResultsEl.className = 'dropdown-no-results';
                noResultsEl.textContent = 'Không tìm thấy ngân hàng nào';
                this.items.appendChild(noResultsEl);
            }
            noResultsEl.style.display = 'block';
        } else if (noResultsEl) {
            noResultsEl.style.display = 'none';
        }
    }
    
    handleKeydown(e) {
        if (!this.isOpen) return;
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.filteredItems.length - 1);
                this.updateHighlight();
                this.scrollToSelected();
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.updateHighlight();
                this.scrollToSelected();
                break;
                
            case 'Enter':
                e.preventDefault();
                if (this.selectedIndex >= 0 && this.filteredItems[this.selectedIndex]) {
                    this.selectItem(this.filteredItems[this.selectedIndex]);
                }
                break;
                
            case 'Escape':
                e.preventDefault();
                this.close();
                break;
        }
    }
    
    updateHighlight() {
        // Remove all highlights
        this.allItems.forEach(item => {
            item.classList.remove('highlighted');
        });
        
        // Add highlight to selected item
        if (this.selectedIndex >= 0 && this.filteredItems[this.selectedIndex]) {
            this.filteredItems[this.selectedIndex].classList.add('highlighted');
        }
    }
    
    scrollToSelected() {
        if (this.selectedIndex >= 0 && this.filteredItems[this.selectedIndex]) {
            const item = this.filteredItems[this.selectedIndex];
            const container = this.items;
            const itemTop = item.offsetTop;
            const itemBottom = itemTop + item.offsetHeight;
            const containerTop = container.scrollTop;
            const containerBottom = containerTop + container.offsetHeight;
            
            if (itemTop < containerTop) {
                container.scrollTop = itemTop;
            } else if (itemBottom > containerBottom) {
                container.scrollTop = itemBottom - container.offsetHeight;
            }
        }
    }
    
    selectItem(item) {
        // Remove previous selection
        this.allItems.forEach(i => i.classList.remove('selected'));
        
        // Add selection to clicked item
        item.classList.add('selected');
        
        // Update search input and hidden input
        const bankName = item.dataset.value;
        const bankCode = item.dataset.code;
        
        this.search.value = bankName;
        this.hiddenInput.value = bankName;
        
        // Update bank code field if exists
        const bankCodeInput = document.getElementById('bankCode');
        if (bankCodeInput) {
            bankCodeInput.value = bankCode;
        }
        
        // Close dropdown
        this.close();
        
        // Trigger change event for form validation
        this.hiddenInput.dispatchEvent(new Event('change'));
    }
    
    reset() {
        this.search.value = '';
        this.hiddenInput.value = '';
        this.allItems.forEach(item => {
            item.classList.remove('selected');
            item.style.display = 'block';
        });
        this.filteredItems = [...this.allItems];
        this.showNoResults(false);
        this.close();
    }
}

// Initialize custom dropdown when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize bank dropdown
    window.bankDropdown = new CustomDropdown('bankDropdown');
});