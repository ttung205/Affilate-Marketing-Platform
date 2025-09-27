// Unified Wallet & Withdrawal Management JavaScript
class WalletManager {
    constructor() {
        this.chart = null;
        this.init();
    }

    init() {
        this.initChart();
        this.initWithdrawalModal();
        this.initEventListeners();
        this.loadChartData();
        this.loadWithdrawals();
    }

    // Chart Management
    initChart() {
        const ctx = document.getElementById('earningsChart');
        if (!ctx) return;

        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Thu nhập (VNĐ)',
                    data: [],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    async loadChartData(period = 30) {
        try {
            const response = await fetch(`/publisher/wallet/earnings-chart?period=${period}`);
            const data = await response.json();

            if (data.success) {
                this.updateChart(data.data);
            }
        } catch (error) {
            console.error('Error loading chart data:', error);
        }
    }

    updateChart(data) {
        if (!this.chart) return;

        this.chart.data.labels = data.labels;
        // Hiển thị dữ liệu gốc với đơn vị VNĐ
        this.chart.data.datasets[0].data = data.earnings;
        this.chart.update();
    }

    // Withdrawal Modal Management
    initWithdrawalModal() {
        const modal = document.getElementById('withdrawalModal');
        if (!modal) return;

        // Reset form when modal is hidden
        modal.addEventListener('hidden.bs.modal', () => {
            this.resetWithdrawalForm();
        });
    }

    // Event Listeners
    initEventListeners() {
        // Chart period change
        const chartPeriod = document.getElementById('chart-period');
        if (chartPeriod) {
            chartPeriod.addEventListener('change', (e) => {
                this.loadChartData(e.target.value);
            });
        }

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

    // Withdrawal Management
    async loadWithdrawals() {
        try {
            const response = await fetch('/publisher/withdrawal/api/list');
            const data = await response.json();

            if (data.success) {
                this.updateWithdrawalsTable(data.data.data);
                this.updateStatsFromWithdrawals(data.data.data);
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
                                onclick="walletManager.viewWithdrawal(${withdrawal.id})"
                                title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${withdrawal.can_be_cancelled ? `
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="walletManager.cancelWithdrawal(${withdrawal.id})"
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

    updateStatsFromWithdrawals(withdrawals) {
        const pendingCount = document.getElementById('pending-count');
        const completedCount = document.getElementById('completed-count');
        const rejectedCount = document.getElementById('rejected-count');

        if (pendingCount) {
            pendingCount.textContent = withdrawals.filter(w => w.status === 'pending').length;
        }
        if (completedCount) {
            completedCount.textContent = withdrawals.filter(w => w.status === 'completed').length;
        }
        if (rejectedCount) {
            rejectedCount.textContent = withdrawals.filter(w => w.status === 'rejected').length;
        }
    }

    // Fee Calculation
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
        const summaryAmount = document.getElementById('summaryAmount');
        const summaryFee = document.getElementById('summaryFee');
        const summaryNetAmount = document.getElementById('summaryNetAmount');

        if (summaryAmount) summaryAmount.textContent = this.formatCurrency(amount) + ' VNĐ';
        if (summaryFee) summaryFee.textContent = this.formatCurrency(fee) + ' VNĐ';
        if (summaryNetAmount) summaryNetAmount.textContent = this.formatCurrency(netAmount) + ' VNĐ';
    }

    resetWithdrawalForm() {
        const form = document.getElementById('withdrawalForm');
        if (form) {
            form.reset();
            this.updateWithdrawalSummary(0, 0, 0);
        }
    }

    // Modal Management
    openWithdrawalModal() {
        const modal = document.getElementById('withdrawalModal');
        if (modal) {
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
        }
    }

    // Withdrawal Actions
    // Form sẽ submit tự động khi click nút submit

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

    // Filter Management
    async applyFilters() {
        const status = document.getElementById('status-filter')?.value;
        const dateFrom = document.getElementById('date-from')?.value;
        const dateTo = document.getElementById('date-to')?.value;

        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);

        try {
            const response = await fetch(`/publisher/withdrawal/api/list?${params}`);
            const data = await response.json();

            if (data.success) {
                this.updateWithdrawalsTable(data.data.data);
                this.updateStatsFromWithdrawals(data.data.data);
            }
        } catch (error) {
            console.error('Error applying filters:', error);
        }
    }

    clearFilters() {
        const statusFilter = document.getElementById('status-filter');
        const dateFrom = document.getElementById('date-from');
        const dateTo = document.getElementById('date-to');

        if (statusFilter) statusFilter.value = '';
        if (dateFrom) dateFrom.value = '';
        if (dateTo) dateTo.value = '';
        this.loadWithdrawals();
    }

    refreshList() {
        this.loadWithdrawals();
    }

    // Utility Functions
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
        // Use toast notifications if available
        if (window.showToast) {
            const toastType = type === 'error' ? 'error' : type === 'success' ? 'success' : 'info';
            return showToast(message, toastType);
        }

        // Fallback to old alert system
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'warning'}`;
        alertDiv.textContent = message;

        const container = document.querySelector('.wallet-container, .withdrawal-container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    }

    async syncWallet() {
        const btn = document.getElementById('sync-wallet-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đồng bộ...';
        }

        try {
            const response = await fetch('/publisher/wallet/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Wallet đã được đồng bộ thành công!', 'success');
                // Reload page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showAlert(data.message || 'Có lỗi xảy ra khi đồng bộ wallet', 'error');
            }
        } catch (error) {
            console.error('Error syncing wallet:', error);
            this.showAlert('Có lỗi xảy ra khi đồng bộ wallet', 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt"></i> Đồng bộ số dư';
            }
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.walletManager = new WalletManager();
});

// Global functions for onclick handlers
function openWithdrawalModal() {
    if (window.walletManager) {
        window.walletManager.openWithdrawalModal();
    }
}

// Function submitWithdrawal đã được thay thế bằng form submit tự động

function applyFilters() {
    if (window.walletManager) {
        window.walletManager.applyFilters();
    }
}

function clearFilters() {
    if (window.walletManager) {
        window.walletManager.clearFilters();
    }
}

function refreshList() {
    if (window.walletManager) {
        window.walletManager.refreshList();
    }
}

function viewWithdrawal(id) {
    if (window.walletManager) {
        window.walletManager.viewWithdrawal(id);
    }
}

function cancelWithdrawal(id) {
    if (window.walletManager) {
        window.walletManager.cancelWithdrawal(id);
    }
}

function syncWallet() {
    if (window.walletManager) {
        window.walletManager.syncWallet();
    }
}
