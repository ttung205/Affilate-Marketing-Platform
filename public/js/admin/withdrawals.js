// Admin Withdrawals Management JavaScript
class AdminWithdrawalsManager {
    constructor() {
        this.selectedWithdrawals = new Set();
        this.init();
    }

    init() {
        this.initEventListeners();
        this.loadWithdrawals();
    }

    initEventListeners() {
        // Filter change events
        const filterInputs = ['status-filter', 'publisher-filter', 'date-from', 'date-to', 'amount-min', 'amount-max'];
        filterInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('change', () => {
                    this.applyFilters();
                });
            }
        });

        // Select all checkbox
        const selectAll = document.getElementById('select-all');
        if (selectAll) {
            selectAll.addEventListener('change', () => {
                this.toggleSelectAll();
            });
        }

        // Individual checkboxes
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('withdrawal-checkbox')) {
                this.updateBulkActions();
            }
        });
    }

    async loadWithdrawals() {
        this.showLoading('withdrawals-tbody');
        
        try {
            const response = await fetch('/admin/withdrawals/api/list');
            const data = await response.json();

            console.log('Admin withdrawals API response:', data); // Debug log
            
            if (data.success) {
                this.updateWithdrawalsTable(data.data || []);
                if (data.stats) {
                    this.updateStats(data.stats);
                }
                if (data.pagination) {
                    this.updatePagination(data.pagination);
                }
            } else {
                this.showError('Không thể tải danh sách yêu cầu rút tiền');
            }
        } catch (error) {
            console.error('Error loading withdrawals:', error);
            this.showError('Có lỗi xảy ra khi tải dữ liệu');
        } finally {
            this.hideLoading();
        }
    }

    async loadStats() {
        try {
            const response = await fetch('/admin/withdrawals/api/stats');
            const data = await response.json();

            if (data.success) {
                this.updateStats(data.data);
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    updateWithdrawalsTable(withdrawals) {
        const tbody = document.getElementById('withdrawals-tbody');
        if (!tbody) return;

        if (withdrawals.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="empty-state">
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
                    <input type="checkbox" class="withdrawal-checkbox" value="${withdrawal.id}">
                </td>
                <td>
                    <span class="withdrawal-id">#${withdrawal.id}</span>
                </td>
                <td>
                    <div class="publisher-info">
                        <div class="publisher-name">${withdrawal.publisher.name}</div>
                        <div class="publisher-email">${withdrawal.publisher.email}</div>
                    </div>
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
                        <i class="${withdrawal.payment_method?.icon || 'fas fa-credit-card'}"></i>
                        <span>${this.getPaymentMethodLabel(withdrawal)}</span>
                    </div>
                </td>
                <td>
                    <span class="status-badge status-${withdrawal.status}">
                        ${withdrawal.status_label || this.getStatusLabel(withdrawal.status)}
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
                                onclick="adminWithdrawalsManager.viewWithdrawal(${withdrawal.id})"
                                title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${withdrawal.status === 'pending' ? `
                            <button class="btn btn-sm btn-success" 
                                    onclick="adminWithdrawalsManager.approveWithdrawal(${withdrawal.id})"
                                    title="Duyệt">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" 
                                    onclick="adminWithdrawalsManager.rejectWithdrawal(${withdrawal.id})"
                                    title="Từ chối">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                        ${withdrawal.status === 'approved' ? `
                            <button class="btn btn-sm btn-primary" 
                                    onclick="adminWithdrawalsManager.completeWithdrawal(${withdrawal.id})"
                                    title="Hoàn thành">
                                <i class="fas fa-check-double"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    updateStats(stats) {
        const pendingCount = document.getElementById('pending-count');
        const approvedCount = document.getElementById('approved-count');
        const completedCount = document.getElementById('completed-count');
        const rejectedCount = document.getElementById('rejected-count');
        const totalAmount = document.getElementById('total-amount');

        if (pendingCount) {
            pendingCount.textContent = stats.pending_count || 0;
        }
        if (approvedCount) {
            approvedCount.textContent = stats.approved_count || 0;
        }
        if (completedCount) {
            completedCount.textContent = stats.completed_count || 0;
        }
        if (rejectedCount) {
            rejectedCount.textContent = stats.rejected_count || 0;
        }
        if (totalAmount) {
            totalAmount.textContent = this.formatCurrency(stats.total_amount || 0) + ' VNĐ';
        }
    }

    updatePagination(pagination) {
        // Update pagination info
        const paginationInfo = document.querySelector('.pagination-info');
        if (paginationInfo) {
            const start = ((pagination.current_page - 1) * pagination.per_page) + 1;
            const end = Math.min(pagination.current_page * pagination.per_page, pagination.total);
            paginationInfo.textContent = `Hiển thị ${start}-${end} trong tổng số ${pagination.total} kết quả`;
        }

        console.log('Pagination updated:', pagination);
    }

    toggleSelectAll() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.withdrawal-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
            if (selectAll.checked) {
                this.selectedWithdrawals.add(checkbox.value);
            } else {
                this.selectedWithdrawals.delete(checkbox.value);
            }
        });

        this.updateBulkActions();
    }

    updateBulkActions() {
        const checkboxes = document.querySelectorAll('.withdrawal-checkbox:checked');
        const bulkActions = document.getElementById('bulk-actions');
        const selectedCount = document.getElementById('selected-count');

        this.selectedWithdrawals.clear();
        checkboxes.forEach(checkbox => {
            this.selectedWithdrawals.add(checkbox.value);
        });

        if (this.selectedWithdrawals.size > 0) {
            bulkActions.style.display = 'flex';
            selectedCount.textContent = this.selectedWithdrawals.size;
        } else {
            bulkActions.style.display = 'none';
        }
    }

    async viewWithdrawal(withdrawalId) {
        try {
            const response = await fetch(`/admin/withdrawals/api/${withdrawalId}`);
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
                    <div class="detail-row">
                        <span class="detail-label">Trạng thái:</span>
                        <span class="detail-value">
                            <span class="status-badge status-${withdrawal.status}">
                                ${withdrawal.status_label}
                            </span>
                        </span>
                    </div>
                </div>

                <div class="detail-section">
                    <h6>Thông tin Publisher</h6>
                    <div class="detail-row">
                        <span class="detail-label">Tên:</span>
                        <span class="detail-value">${withdrawal.publisher.name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${withdrawal.publisher.email}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Số dư hiện tại:</span>
                        <span class="detail-value">${this.formatCurrency(withdrawal.publisher.wallet?.balance || 0)} VNĐ</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h6>Tài khoản thanh toán</h6>
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
                        <span class="detail-value">${withdrawal.payment_method.account_number}</span>
                    </div>
                    ${withdrawal.payment_method.bank_name ? `
                        <div class="detail-row">
                            <span class="detail-label">Ngân hàng:</span>
                            <span class="detail-value">${withdrawal.payment_method.bank_name}</span>
                        </div>
                    ` : ''}
                    ${withdrawal.payment_method.branch_name ? `
                        <div class="detail-row">
                            <span class="detail-label">Chi nhánh:</span>
                            <span class="detail-value">${withdrawal.payment_method.branch_name}</span>
                        </div>
                    ` : ''}
                </div>

                <div class="detail-section">
                    <h6>Thời gian và trạng thái</h6>
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
                    ${withdrawal.admin_notes ? `
                        <div class="detail-row">
                            <span class="detail-label">Ghi chú admin:</span>
                            <span class="detail-value">${withdrawal.admin_notes}</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;

        const modal = new bootstrap.Modal(document.getElementById('withdrawalDetailModal'));
        modal.show();
    }

    approveWithdrawal(withdrawalId) {
        document.getElementById('approveWithdrawalId').value = withdrawalId;
        const modal = new bootstrap.Modal(document.getElementById('approveModal'));
        modal.show();
    }

    rejectWithdrawal(withdrawalId) {
        document.getElementById('rejectWithdrawalId').value = withdrawalId;
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    }

    async completeWithdrawal(withdrawalId) {
        // Hiển thị loading
        this.showAlert('Đang tạo mã QR...', 'info');
        
        try {
            // Gọi API để lấy QR code
            const response = await fetch(`/admin/withdrawals/api/${withdrawalId}/qr-code`);
            const data = await response.json();
            
            if (data.success) {
                // Set withdrawal ID
                document.getElementById('completeWithdrawalId').value = withdrawalId;
                
                // Hiển thị QR code và thông tin
                this.displayQRCode(data.data);
                
                // Hiển thị modal
                const modal = new bootstrap.Modal(document.getElementById('completeModal'));
                modal.show();
            } else {
                this.showAlert(data.message || 'Không thể tạo mã QR', 'error');
            }
        } catch (error) {
            console.error('Error generating QR code:', error);
            this.showAlert('Có lỗi xảy ra khi tạo mã QR', 'error');
        }
    }
    
    displayQRCode(qrData) {
        // Tạo HTML cho QR code section
        const qrSection = document.getElementById('qrCodeSection');
        if (!qrSection) {
            console.error('QR code section not found');
            return;
        }
        
        qrSection.innerHTML = `
            <div class="qr-code-container">
                <div class="qr-code-header">
                    <h6 class="mb-3"><i class="fas fa-qrcode"></i> Quét mã QR để chuyển tiền</h6>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="qr-code-image text-center">
                            <img src="${qrData.qr_url}" alt="QR Code" class="img-fluid" style=" border: 2px solid #dee2e6; border-radius: 8px; padding: 10px;">
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="transfer-info">
                            <h6 class="mb-3">Thông tin chuyển khoản:</h6>
                            
                            <div class="info-group mb-3">
                                <label class="text-muted small">Ngân hàng:</label>
                                <div class="fw-bold">${qrData.bank_name} (${qrData.bank_code})</div>
                            </div>
                            
                            <div class="info-group mb-3">
                                <label class="text-muted small">Số tài khoản:</label>
                                <div class="fw-bold">${qrData.account_no}</div>
                            </div>
                            
                            <div class="info-group mb-3">
                                <label class="text-muted small">Tên tài khoản:</label>
                                <div class="fw-bold">${qrData.account_name}</div>
                            </div>
                            
                            <div class="info-group mb-3">
                                <label class="text-muted small">Số tiền:</label>
                                <div class="fw-bold text-success fs-5">${this.formatCurrency(qrData.amount)} VNĐ</div>
                            </div>
                            
                            <div class="info-group mb-3">
                                <label class="text-muted small">Nội dung chuyển khoản:</label>
                                <div class="fw-bold text-primary">${qrData.description}</div>
                                <small class="text-muted">Vui lòng giữ nguyên nội dung này</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        qrSection.style.display = 'block';
    }

    async confirmApprove() {
        const withdrawalId = document.getElementById('approveWithdrawalId').value;
        const notes = document.getElementById('approveNotes').value;

        try {
            const response = await fetch(`/admin/withdrawals/api/${withdrawalId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ notes })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Yêu cầu rút tiền đã được duyệt', 'success');
                bootstrap.Modal.getInstance(document.getElementById('approveModal')).hide();
                this.loadWithdrawals();
            } else {
                this.showAlert(data.message || 'Có lỗi xảy ra khi duyệt yêu cầu', 'error');
            }
        } catch (error) {
            console.error('Error approving withdrawal:', error);
            this.showAlert('Có lỗi xảy ra khi duyệt yêu cầu', 'error');
        }
    }

    async confirmReject() {
        const withdrawalId = document.getElementById('rejectWithdrawalId').value;
        const reason = document.getElementById('rejectReason').value;
        const notes = document.getElementById('rejectNotes').value;

        if (!reason.trim()) {
            this.showAlert('Vui lòng nhập lý do từ chối', 'error');
            return;
        }

        try {
            const response = await fetch(`/admin/withdrawals/api/${withdrawalId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    reason: reason,
                    notes: notes 
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Yêu cầu rút tiền đã bị từ chối', 'success');
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                this.loadWithdrawals();
            } else {
                this.showAlert(data.message || 'Có lỗi xảy ra khi từ chối yêu cầu', 'error');
            }
        } catch (error) {
            console.error('Error rejecting withdrawal:', error);
            this.showAlert('Có lỗi xảy ra khi từ chối yêu cầu', 'error');
        }
    }

    async confirmComplete() {
        const withdrawalId = document.getElementById('completeWithdrawalId').value;
        const transactionReference = document.getElementById('transactionReference').value;
        const notes = document.getElementById('completeNotes').value;

        if (!transactionReference.trim()) {
            this.showAlert('Vui lòng nhập mã giao dịch', 'error');
            return;
        }

        try {
            const response = await fetch(`/admin/withdrawals/api/${withdrawalId}/complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    transaction_reference: transactionReference,
                    admin_notes: notes 
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Yêu cầu rút tiền đã được hoàn thành', 'success');
                bootstrap.Modal.getInstance(document.getElementById('completeModal')).hide();
                this.loadWithdrawals();
            } else {
                this.showAlert(data.message || 'Có lỗi xảy ra khi hoàn thành yêu cầu', 'error');
            }
        } catch (error) {
            console.error('Error completing withdrawal:', error);
            this.showAlert('Có lỗi xảy ra khi hoàn thành yêu cầu', 'error');
        }
    }

    async bulkApprove() {
        if (this.selectedWithdrawals.size === 0) {
            this.showAlert('Vui lòng chọn ít nhất một yêu cầu', 'error');
            return;
        }

        if (!confirm(`Bạn có chắc chắn muốn duyệt ${this.selectedWithdrawals.size} yêu cầu rút tiền?`)) {
            return;
        }

        try {
            const response = await fetch('/admin/withdrawals/bulk-action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    action: 'approve',
                    withdrawal_ids: Array.from(this.selectedWithdrawals)
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert(`${data.approved_count} yêu cầu đã được duyệt`, 'success');
                this.selectedWithdrawals.clear();
                this.updateBulkActions();
                this.loadWithdrawals();
            } else {
                this.showAlert(data.message || 'Có lỗi xảy ra khi duyệt hàng loạt', 'error');
            }
        } catch (error) {
            console.error('Error bulk approving withdrawals:', error);
            this.showAlert('Có lỗi xảy ra khi duyệt hàng loạt', 'error');
        }
    }

    async bulkReject() {
        if (this.selectedWithdrawals.size === 0) {
            this.showAlert('Vui lòng chọn ít nhất một yêu cầu', 'error');
            return;
        }

        const reason = prompt('Nhập lý do từ chối:');
        if (!reason || !reason.trim()) {
            this.showAlert('Vui lòng nhập lý do từ chối', 'error');
            return;
        }

        if (!confirm(`Bạn có chắc chắn muốn từ chối ${this.selectedWithdrawals.size} yêu cầu rút tiền?`)) {
            return;
        }

        try {
            const response = await fetch('/admin/withdrawals/bulk-action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    action: 'reject',
                    withdrawal_ids: Array.from(this.selectedWithdrawals),
                    rejection_reason: reason
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert(`${data.rejected_count} yêu cầu đã bị từ chối`, 'success');
                this.selectedWithdrawals.clear();
                this.updateBulkActions();
                this.loadWithdrawals();
            } else {
                this.showAlert(data.message || 'Có lỗi xảy ra khi từ chối hàng loạt', 'error');
            }
        } catch (error) {
            console.error('Error bulk rejecting withdrawals:', error);
            this.showAlert('Có lỗi xảy ra khi từ chối hàng loạt', 'error');
        }
    }

    async applyFilters() {
        const status = document.getElementById('status-filter').value;
        const publisher = document.getElementById('publisher-filter').value;
        const dateFrom = document.getElementById('date-from').value;
        const dateTo = document.getElementById('date-to').value;
        const amountMin = document.getElementById('amount-min').value;
        const amountMax = document.getElementById('amount-max').value;

        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (publisher) params.append('publisher', publisher);
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);
        if (amountMin) params.append('amount_min', amountMin);
        if (amountMax) params.append('amount_max', amountMax);

        try {
            const response = await fetch(`/admin/withdrawals/api/list?${params}`);
            const data = await response.json();

            if (data.success) {
                this.updateWithdrawalsTable(data.withdrawals);
                this.updateStats(data.stats);
            }
        } catch (error) {
            console.error('Error applying filters:', error);
        }
    }

    clearFilters() {
        document.getElementById('status-filter').value = '';
        document.getElementById('publisher-filter').value = '';
        document.getElementById('date-from').value = '';
        document.getElementById('date-to').value = '';
        document.getElementById('amount-min').value = '';
        document.getElementById('amount-max').value = '';
        this.loadWithdrawals();
    }

    refreshData() {
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

    getStatusLabel(status) {
        const statusLabels = {
            'pending': 'Chờ duyệt',
            'approved': 'Đã duyệt',
            'completed': 'Hoàn thành',
            'rejected': 'Từ chối',
            'cancelled': 'Đã hủy'
        };
        return statusLabels[status] || status;
    }

    getPaymentMethodLabel(withdrawal) {
        // Try payment_method relationship first
        if (withdrawal.payment_method?.type_label) {
            return withdrawal.payment_method.type_label;
        }
        
        // Fallback to payment_method_type
        const typeLabels = {
            'bank_transfer': 'Chuyển khoản ngân hàng',
            'momo': 'Ví MoMo',
            'zalopay': 'Ví ZaloPay',
            'vnpay': 'Ví VNPay',
            'phone_card': 'Thẻ cào điện thoại'
        };
        
        return typeLabels[withdrawal.payment_method_type] || withdrawal.payment_method_type || 'Chưa xác định';
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
        const container = document.querySelector('.withdrawals-container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    }

    showLoading(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = `
                <tr>
                    <td colspan="8" class="loading-state">
                        <div class="loading-spinner">
                            <div class="spinner"></div>
                            <span>Đang tải dữ liệu...</span>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    hideLoading() {
        const loadingElements = document.querySelectorAll('.loading-state');
        loadingElements.forEach(element => {
            element.remove();
        });
    }

    showError(message) {
        const tbody = document.getElementById('withdrawals-tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="error-state">
                        <div class="error-content">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>${message}</span>
                            <button class="btn btn-outline-primary btn-sm" onclick="adminWithdrawalsManager.loadWithdrawals()">
                                <i class="fas fa-redo"></i> Thử lại
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.adminWithdrawalsManager = new AdminWithdrawalsManager();
});

// Global functions for onclick handlers
function refreshData() {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.refreshData();
    }
}

function applyFilters() {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.applyFilters();
    }
}

function clearFilters() {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.clearFilters();
    }
}

function refreshList() {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.refreshList();
    }
}

function toggleSelectAll() {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.toggleSelectAll();
    }
}

function bulkApprove() {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.bulkApprove();
    }
}

function bulkReject() {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.bulkReject();
    }
}

function viewWithdrawal(id) {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.viewWithdrawal(id);
    }
}

function approveWithdrawal(id) {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.approveWithdrawal(id);
    }
}

function rejectWithdrawal(id) {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.rejectWithdrawal(id);
    }
}

function completeWithdrawal(id) {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.completeWithdrawal(id);
    }
}

function confirmApprove() {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.confirmApprove();
    }
}

function confirmReject() {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.confirmReject();
    }
}

function confirmComplete() {
    if (window.adminWithdrawalsManager) {
        window.adminWithdrawalsManager.confirmComplete();
    }
}
