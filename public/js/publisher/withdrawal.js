// Withdrawal Management JavaScript
class WithdrawalManager {
    constructor() {
        this.init();
    }

    init() {
        this.initWithdrawalModal();
        this.initEventListeners();
        this.loadWithdrawals();
    }

    initWithdrawalModal() {
        const modal = document.getElementById('withdrawalModal');
        if (!modal) return;

        // Reset form when modal is hidden
        modal.addEventListener('hidden.bs.modal', () => {
            this.resetWithdrawalForm();
        });
    }

    initEventListeners() {
        // Withdrawal amount change
        const withdrawalAmount = document.getElementById('withdrawalAmount');
        if (withdrawalAmount) {
            withdrawalAmount.addEventListener('input', () => {
                this.calculateWithdrawalFee();
            });
        }

        // Payment method change
        const paymentMethod = document.getElementById('paymentMethod');
        if (paymentMethod) {
            paymentMethod.addEventListener('change', () => {
                this.calculateWithdrawalFee();
            });
        }

        // Status filter change
        const statusFilter = document.getElementById('status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', () => {
                this.applyFilters();
            });
        }

        // Date filters
        const dateFrom = document.getElementById('date-from');
        const dateTo = document.getElementById('date-to');
        
        if (dateFrom) {
            dateFrom.addEventListener('change', () => {
                this.applyFilters();
            });
        }
        
        if (dateTo) {
            dateTo.addEventListener('change', () => {
                this.applyFilters();
            });
        }
    }

    async loadWithdrawals() {
        try {
            const response = await fetch('/publisher/withdrawal/api/list');
            const data = await response.json();

            if (data.success) {
                this.updateWithdrawalsTable(data.withdrawals);
                this.updateStats(data.stats);
            }
        } catch (error) {
            console.error('Error loading withdrawals:', error);
        }
    }

    updateWithdrawalsTable(withdrawals) {
        const tbody = document.getElementById('withdrawals-tbody');
        if (!tbody) return;

        if (withdrawals.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="empty-state">
                        <i class="fas fa-money-bill-wave"></i>
                        <p>Chưa có yêu cầu rút tiền nào</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = withdrawals.map(withdrawal => `
            <tr data-withdrawal-id="${withdrawal.id}">
                <td>
                    <span class="withdrawal-id">#${withdrawal.id}</span>
                </td>
                <td>
                    <span class="withdrawal-amount">
                        ${this.formatCurrency(withdrawal.amount)} VNĐ
                    </span>
                    ${withdrawal.fee > 0 ? `
                        <br>
                        <small class="text-muted">
                            Phí: ${this.formatCurrency(withdrawal.fee)} VNĐ
                        </small>
                    ` : ''}
                </td>
                <td>
                    <div class="payment-method">
                        <i class="${withdrawal.payment_method.icon}"></i>
                        <span>${withdrawal.payment_method.type_label}</span>
                    </div>
                </td>
                <td>
                    <span class="status-badge status-${withdrawal.status}">
                        ${withdrawal.status_label}
                    </span>
                </td>
                <td>
                    <span class="withdrawal-date">
                        ${this.formatDate(withdrawal.created_at)}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm btn-outline-primary" 
                                onclick="withdrawalManager.viewWithdrawal(${withdrawal.id})"
                                title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${withdrawal.can_be_cancelled ? `
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="withdrawalManager.cancelWithdrawal(${withdrawal.id})"
                                    title="Hủy yêu cầu">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    updateStats(stats) {
        const availableBalance = document.getElementById('available-balance');
        const pendingCount = document.getElementById('pending-count');
        const completedCount = document.getElementById('completed-count');
        const rejectedCount = document.getElementById('rejected-count');

        if (availableBalance) {
            availableBalance.textContent = this.formatCurrency(stats.available_balance) + ' VNĐ';
        }
        if (pendingCount) {
            pendingCount.textContent = stats.pending_count || 0;
        }
        if (completedCount) {
            completedCount.textContent = stats.completed_count || 0;
        }
        if (rejectedCount) {
            rejectedCount.textContent = stats.rejected_count || 0;
        }
    }

    calculateWithdrawalFee() {
        const amount = parseFloat(document.getElementById('withdrawalAmount').value) || 0;
        const paymentMethodSelect = document.getElementById('paymentMethod');
        const selectedOption = paymentMethodSelect.options[paymentMethodSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            this.updateWithdrawalSummary(0, 0, 0);
            return;
        }

        const feeRate = parseFloat(selectedOption.dataset.feeRate) || 0;
        const fee = amount * feeRate;
        const netAmount = amount - fee;

        this.updateWithdrawalSummary(amount, fee, netAmount);
    }

    updateWithdrawalSummary(amount, fee, netAmount) {
        document.getElementById('summaryAmount').textContent = this.formatCurrency(amount) + ' VNĐ';
        document.getElementById('summaryFee').textContent = this.formatCurrency(fee) + ' VNĐ';
        document.getElementById('summaryNetAmount').textContent = this.formatCurrency(netAmount) + ' VNĐ';
    }

    resetWithdrawalForm() {
        document.getElementById('withdrawalForm').reset();
        this.updateWithdrawalSummary(0, 0, 0);
    }

    openWithdrawalModal() {
        const modal = new bootstrap.Modal(document.getElementById('withdrawalModal'));
        modal.show();
    }

    async submitWithdrawal() {
        const form = document.getElementById('withdrawalForm');
        const formData = new FormData(form);
        
        const amount = parseFloat(document.getElementById('withdrawalAmount').value);
        const paymentMethodId = document.getElementById('paymentMethod').value;

        if (!amount || !paymentMethodId) {
            this.showAlert('Vui lòng nhập đầy đủ thông tin', 'error');
            return;
        }

        if (amount < 10000) {
            this.showAlert('Số tiền tối thiểu là 10,000 VNĐ', 'error');
            return;
        }

        try {
            const response = await fetch('/publisher/withdrawal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    amount: amount,
                    payment_method_id: paymentMethodId
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Yêu cầu rút tiền đã được gửi thành công', 'success');
                bootstrap.Modal.getInstance(document.getElementById('withdrawalModal')).hide();
                this.resetWithdrawalForm();
                this.loadWithdrawals();
            } else {
                this.showAlert(data.message || 'Có lỗi xảy ra khi gửi yêu cầu rút tiền', 'error');
            }
        } catch (error) {
            console.error('Error submitting withdrawal:', error);
            this.showAlert('Có lỗi xảy ra khi gửi yêu cầu rút tiền', 'error');
        }
    }

    async viewWithdrawal(withdrawalId) {
        try {
            const response = await fetch(`/publisher/withdrawal/api/${withdrawalId}`);
            const data = await response.json();

            if (data.success) {
                this.showWithdrawalDetail(data.withdrawal);
            } else {
                this.showAlert('Không thể tải thông tin yêu cầu rút tiền', 'error');
            }
        } catch (error) {
            console.error('Error loading withdrawal details:', error);
            this.showAlert('Có lỗi xảy ra khi tải thông tin', 'error');
        }
    }

    showWithdrawalDetail(withdrawal) {
        const content = document.getElementById('withdrawalDetailContent');
        if (!content) return;

        content.innerHTML = `
            <div class="withdrawal-detail">
                <div class="detail-section">
                    <h6>Thông tin cơ bản</h6>
                    <div class="detail-row">
                        <span class="detail-label">Mã yêu cầu:</span>
                        <span class="detail-value">#${withdrawal.id}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Số tiền:</span>
                        <span class="detail-value">${this.formatCurrency(withdrawal.amount)} VNĐ</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phí giao dịch:</span>
                        <span class="detail-value">${this.formatCurrency(withdrawal.fee)} VNĐ</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Số tiền thực nhận:</span>
                        <span class="detail-value">${this.formatCurrency(withdrawal.net_amount)} VNĐ</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h6>Phương thức thanh toán</h6>
                    <div class="detail-row">
                        <span class="detail-label">Loại:</span>
                        <span class="detail-value">${withdrawal.payment_method.type_label}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Tên tài khoản:</span>
                        <span class="detail-value">${withdrawal.payment_method.account_name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Số tài khoản:</span>
                        <span class="detail-value">${withdrawal.payment_method.masked_account_number}</span>
                    </div>
                    ${withdrawal.payment_method.bank_name ? `
                        <div class="detail-row">
                            <span class="detail-label">Ngân hàng:</span>
                            <span class="detail-value">${withdrawal.payment_method.bank_name}</span>
                        </div>
                    ` : ''}
                </div>

                <div class="detail-section">
                    <h6>Trạng thái</h6>
                    <div class="detail-row">
                        <span class="detail-label">Trạng thái hiện tại:</span>
                        <span class="detail-value">
                            <span class="status-badge status-${withdrawal.status}">
                                ${withdrawal.status_label}
                            </span>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Ngày tạo:</span>
                        <span class="detail-value">${this.formatDateTime(withdrawal.created_at)}</span>
                    </div>
                    ${withdrawal.processed_at ? `
                        <div class="detail-row">
                            <span class="detail-label">Ngày xử lý:</span>
                            <span class="detail-value">${this.formatDateTime(withdrawal.processed_at)}</span>
                        </div>
                    ` : ''}
                    ${withdrawal.completed_at ? `
                        <div class="detail-row">
                            <span class="detail-label">Ngày hoàn thành:</span>
                            <span class="detail-value">${this.formatDateTime(withdrawal.completed_at)}</span>
                        </div>
                    ` : ''}
                    ${withdrawal.rejection_reason ? `
                        <div class="detail-row">
                            <span class="detail-label">Lý do từ chối:</span>
                            <span class="detail-value">${withdrawal.rejection_reason}</span>
                        </div>
                    ` : ''}
                    ${withdrawal.transaction_reference ? `
                        <div class="detail-row">
                            <span class="detail-label">Mã giao dịch:</span>
                            <span class="detail-value">${withdrawal.transaction_reference}</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;

        const modal = new bootstrap.Modal(document.getElementById('withdrawalDetailModal'));
        modal.show();
    }

    async cancelWithdrawal(withdrawalId) {
        if (!confirm('Bạn có chắc chắn muốn hủy yêu cầu rút tiền này?')) {
            return;
        }

        try {
            const response = await fetch(`/publisher/withdrawal/${withdrawalId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Yêu cầu rút tiền đã được hủy', 'success');
                this.loadWithdrawals();
            } else {
                this.showAlert(data.message || 'Có lỗi xảy ra khi hủy yêu cầu', 'error');
            }
        } catch (error) {
            console.error('Error cancelling withdrawal:', error);
            this.showAlert('Có lỗi xảy ra khi hủy yêu cầu', 'error');
        }
    }

    async applyFilters() {
        const status = document.getElementById('status-filter').value;
        const dateFrom = document.getElementById('date-from').value;
        const dateTo = document.getElementById('date-to').value;

        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);

        try {
            const response = await fetch(`/publisher/withdrawal/api/list?${params}`);
            const data = await response.json();

            if (data.success) {
                this.updateWithdrawalsTable(data.withdrawals);
            }
        } catch (error) {
            console.error('Error applying filters:', error);
        }
    }

    clearFilters() {
        document.getElementById('status-filter').value = '';
        document.getElementById('date-from').value = '';
        document.getElementById('date-to').value = '';
        this.loadWithdrawals();
    }

    refreshList() {
        this.loadWithdrawals();
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount);
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    showAlert(message, type = 'info') {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());

        // Create new alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'error' : type === 'success' ? 'success' : 'warning'}`;
        alertDiv.textContent = message;

        // Insert at top of container
        const container = document.querySelector('.withdrawal-container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.withdrawalManager = new WithdrawalManager();
});

// Global functions for onclick handlers
function openWithdrawalModal() {
    if (window.withdrawalManager) {
        window.withdrawalManager.openWithdrawalModal();
    }
}

function submitWithdrawal() {
    if (window.withdrawalManager) {
        window.withdrawalManager.submitWithdrawal();
    }
}

function applyFilters() {
    if (window.withdrawalManager) {
        window.withdrawalManager.applyFilters();
    }
}

function clearFilters() {
    if (window.withdrawalManager) {
        window.withdrawalManager.clearFilters();
    }
}

function refreshList() {
    if (window.withdrawalManager) {
        window.withdrawalManager.refreshList();
    }
}

function viewWithdrawal(id) {
    if (window.withdrawalManager) {
        window.withdrawalManager.viewWithdrawal(id);
    }
}

function cancelWithdrawal(id) {
    if (window.withdrawalManager) {
        window.withdrawalManager.cancelWithdrawal(id);
    }
}
