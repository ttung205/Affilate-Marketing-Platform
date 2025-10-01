// Withdrawal Management JavaScript
class WithdrawalManager {
    constructor() {
        this.currentSessionKey = null;
        this.isOTPStep = false;
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
        // Prevent form default submission
        const withdrawalForm = document.getElementById('withdrawalForm');
        if (withdrawalForm) {
            withdrawalForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitWithdrawal();
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

    async loadWithdrawals() {
        try {
            const response = await fetch('/publisher/withdrawal/api/list');
            const data = await response.json();

            if (data.success && data.data) {
                this.updateWithdrawalsTable(data.data.data || []);
                this.updatePagination(data.data);
            }
        } catch (error) {
            console.error('Error loading withdrawals:', error);
        }
    }

    async loadStats() {
        try {
            const response = await fetch('/publisher/withdrawal/api/stats');
            const data = await response.json();

            if (data.success) {
                this.updateStats(data.data);
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    updatePagination(paginationData) {
        // Update pagination if exists
        const paginationContainer = document.querySelector('.pagination-container');
        if (paginationContainer && paginationData.last_page > 1) {
            // Simple pagination display
            paginationContainer.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span>Hiển thị ${paginationData.from || 0} - ${paginationData.to || 0} của ${paginationData.total} kết quả</span>
                    <span>Trang ${paginationData.current_page} / ${paginationData.last_page}</span>
                </div>
            `;
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
        // Check if we need to reset modal content before changing state
        const needsReset = this.isOTPStep;
        
        // Reset OTP state
        this.isOTPStep = false;
        this.currentSessionKey = null;
        
        // Reset form if it exists
        const form = document.getElementById('withdrawalForm');
        if (form) {
            form.reset();
            this.updateWithdrawalSummary(0, 0, 0);
        }
        
        // Reset modal content to original form if needed
        if (needsReset) {
            this.resetModalContent();
        }
    }

    resetModalContent() {
        // Restore original modal content
        const modalBody = document.querySelector('#withdrawalModal .modal-body');
        const modalFooter = document.querySelector('#withdrawalModal .modal-footer');
        
        if (modalBody) {
            // Restore original form HTML
            modalBody.innerHTML = `
                <form id="withdrawalForm" method="POST">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số tiền muốn rút (VNĐ)</label>
                                <input type="number" class="form-control" id="withdrawalAmount" name="amount"
                                       min="10000" step="1000" required>
                                <div class="form-text">
                                    Số dư khả dụng: <span id="availableBalanceText">
                                        ${document.getElementById('availableBalanceText')?.textContent || '0 VNĐ'}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phương thức thanh toán</label>
                                <select class="form-select" id="paymentMethod" name="payment_method_id" required>
                                    <option value="">Chọn phương thức</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="withdrawal-summary">
                        <div class="summary-row">
                            <span>Số tiền rút:</span>
                            <span id="summaryAmount">0 VNĐ</span>
                        </div>
                        <div class="summary-row">
                            <span>Phí giao dịch:</span>
                            <span id="summaryFee">0 VNĐ</span>
                        </div>
                        <div class="summary-row total">
                            <span>Số tiền thực nhận:</span>
                            <span id="summaryNetAmount">0 VNĐ</span>
                        </div>
                    </div>
                </form>
            `;
        }
        
        if (modalFooter) {
            // Restore original footer
            modalFooter.innerHTML = `
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="submitWithdrawal()">Tạo yêu cầu</button>
            `;
        }
        
        // Re-initialize event listeners for the new form
        this.initEventListeners();
        
        // Load payment methods again
        this.loadPaymentMethods();
    }

    async loadPaymentMethods() {
        try {
            const response = await fetch('/publisher/payment-methods/api/list');
            const data = await response.json();
            
            console.log('Payment methods API response:', data); // Debug log
            
            if (data.success && data.payment_methods) {
                const select = document.getElementById('paymentMethod');
                if (select) {
                    // Clear existing options except the first one
                    select.innerHTML = '<option value="">Chọn phương thức</option>';
                    
                    // Add payment methods
                    data.payment_methods.forEach(method => {
                        const option = document.createElement('option');
                        option.value = method.id;
                        option.textContent = `${method.type_label} - ${method.masked_account_number}`;
                        if (method.is_default) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                    
                    console.log(`Loaded ${data.payment_methods.length} payment methods`); // Debug log
                }
            } else {
                console.error('Invalid payment methods response:', data);
            }
        } catch (error) {
            console.error('Error loading payment methods:', error);
        }
    }

    openWithdrawalModal() {
        // Always reset form when opening modal
        this.resetWithdrawalForm();
        
        const modal = new bootstrap.Modal(document.getElementById('withdrawalModal'));
        modal.show();
    }

    async submitWithdrawal() {
        // If in OTP step, we don't need the form, just get OTP input
        if (this.isOTPStep) {
            const otpInput = document.getElementById('otpInput');
            if (!otpInput) {
                console.error('OTP input not found');
                this.showAlert('Không tìm thấy ô nhập OTP', 'error');
                return;
            }
            
            const otp = otpInput.value;
            if (!otp || otp.length !== 6) {
                this.showAlert('Vui lòng nhập mã OTP 6 chữ số', 'error');
                return;
            }
            
            // Submit with OTP
            await this.submitWithOTP(otp);
            return;
        }
        
        // Normal submission - get form data
        const form = document.getElementById('withdrawalForm');
        if (!form) {
            console.error('Withdrawal form not found');
            this.showAlert('Không tìm thấy form rút tiền', 'error');
            return;
        }
        
        const amountInput = document.getElementById('withdrawalAmount');
        const paymentMethodInput = document.getElementById('paymentMethod');
        
        if (!amountInput || !paymentMethodInput) {
            console.error('Form inputs not found');
            this.showAlert('Không tìm thấy các trường nhập liệu', 'error');
            return;
        }
        
        const amount = parseFloat(amountInput.value);
        const paymentMethodId = paymentMethodInput.value;

        if (!amount || !paymentMethodId) {
            this.showAlert('Vui lòng nhập đầy đủ thông tin', 'error');
            return;
        }

        if (amount < 100000) {
            this.showAlert('Số tiền tối thiểu là 100,000 VNĐ', 'error');
            return;
        }

        // Prepare request data
        const requestData = {
            amount: amount,
            payment_method_id: paymentMethodId
        };

        // If in OTP step, add OTP data
        if (this.isOTPStep && this.currentWithdrawalId) {
            requestData.withdrawal_id = this.currentWithdrawalId;
            requestData.otp = document.getElementById('otpInput')?.value;
            
            if (!requestData.otp || requestData.otp.length !== 6) {
                this.showAlert('Vui lòng nhập mã OTP 6 chữ số', 'error');
                return;
            }
        }

        try {
            const response = await fetch('/publisher/withdrawal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData)
            });

            const data = await response.json();

            if (data.success) {
                if (data.requires_otp) {
                    // Show OTP step in modal
                    this.currentSessionKey = data.withdrawal_session_key;
                    this.showOTPStep(data.message);
                } else {
                    // Success - withdrawal completed
                    this.showAlert('Yêu cầu rút tiền đã được gửi thành công', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('withdrawalModal')).hide();
                    this.resetWithdrawalForm();
                    this.loadWithdrawals();
                }
            } else {
                this.showAlert(data.message || 'Có lỗi xảy ra khi gửi yêu cầu rút tiền', 'error');
            }
        } catch (error) {
            console.error('Error submitting withdrawal:', error);
            this.showAlert('Có lỗi xảy ra khi gửi yêu cầu rút tiền', 'error');
        }
    }

    async submitWithOTP(otp) {
        if (!this.currentSessionKey) {
            this.showAlert('Không tìm thấy thông tin yêu cầu rút tiền', 'error');
            return;
        }

        const requestData = {
            withdrawal_session_key: this.currentSessionKey,
            otp: otp
        };

        try {
            const response = await fetch('/publisher/withdrawal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData)
            });

            const data = await response.json();

            // Handle validation errors
            if (response.status === 422) {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat();
                    this.showAlert(errorMessages.join(', '), 'error');
                } else {
                    this.showAlert(data.message || 'Dữ liệu không hợp lệ', 'error');
                }
                return;
            }

            if (data.success) {
                this.showAlert('Yêu cầu rút tiền đã được gửi thành công', 'success');
                bootstrap.Modal.getInstance(document.getElementById('withdrawalModal')).hide();
                this.resetWithdrawalForm();
                this.loadWithdrawals();
            } else {
                this.showAlert(data.message || 'Có lỗi xảy ra khi xác thực OTP', 'error');
            }
        } catch (error) {
            console.error('Error submitting OTP:', error);
            this.showAlert('Có lỗi xảy ra khi xác thực OTP', 'error');
        }
    }

    showOTPStep(message) {
        this.isOTPStep = true;
        
        // Update modal content to show OTP form
        const modalBody = document.querySelector('#withdrawalModal .modal-body');
        if (modalBody) {
            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h5>Xác thực bảo mật</h5>
                        <div class="alert alert-info">
                            ${message}
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nhập mã OTP (6 chữ số)</label>
                        <input type="text" 
                               id="otpInput" 
                               class="form-control text-center" 
                               style="font-size: 1.5rem; letter-spacing: 0.5rem; font-family: monospace;"
                               maxlength="6" 
                               pattern="[0-9]{6}"
                               placeholder="000000"
                               required>
                        <small class="form-text text-muted">
                            Mã OTP có hiệu lực trong 10 phút
                        </small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.withdrawalManager.resendOTP()">
                            <i class="fas fa-redo"></i> Gửi lại mã OTP
                        </button>
                    </div>
                </div>
            `;
        }
        
        // Update modal footer
        const modalFooter = document.querySelector('#withdrawalModal .modal-footer');
        if (modalFooter) {
            modalFooter.innerHTML = `
                <button type="button" class="btn btn-secondary" onclick="window.withdrawalManager.cancelOTP()">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="window.withdrawalManager.submitWithdrawal()">
                    <i class="fas fa-check"></i> Xác nhận rút tiền
                </button>
            `;
        }
        
        // Focus on OTP input
        setTimeout(() => {
            const otpInput = document.getElementById('otpInput');
            if (otpInput) {
                otpInput.focus();
            }
        }, 100);
    }

    async resendOTP() {
        if (!this.currentSessionKey) {
            this.showAlert('Không tìm thấy thông tin yêu cầu rút tiền', 'error');
            return;
        }

        try {
            const response = await fetch('/publisher/withdrawal/otp/resend', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    withdrawal_session_key: this.currentSessionKey
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert(result.message, 'success');
            } else {
                this.showAlert(result.message || 'Không thể gửi lại mã OTP', 'error');
            }
        } catch (error) {
            console.error('Resend OTP error:', error);
            this.showAlert('Có lỗi xảy ra khi gửi lại mã OTP', 'error');
        }
    }

    cancelOTP() {
        this.isOTPStep = false;
        this.currentSessionKey = null;
        bootstrap.Modal.getInstance(document.getElementById('withdrawalModal')).hide();
        this.resetWithdrawalForm();
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
    try {
        window.withdrawalManager = new WithdrawalManager();
    } catch (error) {
        console.error('Error initializing WithdrawalManager:', error);
    }
});

// Global functions for onclick handlers
function openWithdrawalModal() {
    if (window.withdrawalManager) {
        window.withdrawalManager.openWithdrawalModal();
    } else {
        console.error('WithdrawalManager not initialized');
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
