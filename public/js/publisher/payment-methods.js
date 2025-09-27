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
                showError(data.message || 'Không thể tải danh sách phương thức thanh toán');
            }
        } catch (error) {
            console.error('Error loading payment methods:', error);
            showError('Có lỗi xảy ra khi tải danh sách phương thức thanh toán');
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
                    <h3>Chưa có phương thức thanh toán</h3>
                    <p>Thêm phương thức thanh toán đầu tiên để bắt đầu rút tiền</p>
                    <button class="btn btn-primary" onclick="paymentMethodManager.openPaymentMethodModal()">
                        <i class="fas fa-plus"></i>
                        Thêm phương thức
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
        const typeSelect = document.getElementById('methodType');
        const bankNameField = document.getElementById('bankNameField');
        const bankDetailsFields = document.getElementById('bankDetailsFields');

        if (typeSelect) {
            typeSelect.addEventListener('change', (e) => {
                if (e.target.value === 'bank_transfer') {
                    bankNameField.style.display = 'block';
                    bankDetailsFields.style.display = 'block';
                    this.loadBanks();
                } else {
                    bankNameField.style.display = 'none';
                    bankDetailsFields.style.display = 'none';
                }
            });
        }
    }

    async loadBanks() {
        try {
            const response = await fetch('/publisher/payment-methods/api/banks');
            const data = await response.json();

            if (data.success) {
                const bankSelect = document.getElementById('bankName');
                if (bankSelect) {
                    bankSelect.innerHTML = '<option value="">Chọn ngân hàng</option>' +
                        data.banks.map(bank => `<option value="${bank.name}" data-code="${bank.code}">${bank.name}</option>`).join('');
                    
                    // Add event listener for bank selection
                    bankSelect.addEventListener('change', (e) => {
                        const selectedOption = e.target.selectedOptions[0];
                        if (selectedOption) {
                            document.getElementById('bankCode').value = selectedOption.dataset.code || '';
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Error loading banks:', error);
        }
    }

    resetPaymentMethodForm() {
        const form = document.getElementById('paymentMethodForm');
        if (form) {
            form.reset();
            // Reset modal title
            document.getElementById('modalTitle').textContent = 'Thêm phương thức thanh toán';
            // Hide bank fields
            document.getElementById('bankNameField').style.display = 'none';
            document.getElementById('bankDetailsFields').style.display = 'none';
            // Hide preview
            document.getElementById('paymentMethodPreview').style.display = 'none';
        }
    }

    // Form sẽ submit tự động khi click nút submit

    async editPaymentMethod(id) {
        showInfo('Tính năng chỉnh sửa đang được phát triển');
    }

    async setAsDefault(id) {
        if (!confirm('Bạn có chắc chắn muốn đặt phương thức này làm mặc định?')) {
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
                showError(result.message || 'Có lỗi xảy ra khi đặt phương thức mặc định');
            }
        } catch (error) {
            console.error('Error setting default payment method:', error);
            showError('Có lỗi xảy ra khi đặt phương thức mặc định');
        }
    }

    async deletePaymentMethod(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa phương thức thanh toán này?')) {
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
                showError(result.message || 'Có lỗi xảy ra khi xóa phương thức thanh toán');
            }
        } catch (error) {
            console.error('Error deleting payment method:', error);
            showError('Có lỗi xảy ra khi xóa phương thức thanh toán');
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
