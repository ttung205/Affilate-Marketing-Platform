// Wallet Dashboard JavaScript
class WalletDashboard {
    constructor() {
        this.chart = null;
        this.init();
    }

    init() {
        this.initChart();
        this.initWithdrawalModal();
        this.initEventListeners();
        this.loadChartData();
    }

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

    initWithdrawalModal() {
        const modal = document.getElementById('withdrawalModal');
        if (!modal) return;

        // Reset form when modal is hidden
        modal.addEventListener('hidden.bs.modal', () => {
            this.resetWithdrawalForm();
        });
    }

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
        this.chart.data.datasets[0].data = data.earnings;
        this.chart.update();
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
        document.getElementById('summaryAmount').textContent = this.formatCurrency(amount);
        document.getElementById('summaryFee').textContent = this.formatCurrency(fee);
        document.getElementById('summaryNetAmount').textContent = this.formatCurrency(netAmount);
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + ' VNĐ';
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
                // Reload page to update wallet data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showAlert(data.message || 'Có lỗi xảy ra khi gửi yêu cầu rút tiền', 'error');
            }
        } catch (error) {
            console.error('Error submitting withdrawal:', error);
            this.showAlert('Có lỗi xảy ra khi gửi yêu cầu rút tiền', 'error');
        }
    }

    showAlert(message, type = 'info') {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());

        // Create new alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'error' : type === 'success' ? 'success' : 'warning'}`;
        alertDiv.textContent = message;

        // Insert at top of wallet container
        const walletContainer = document.querySelector('.wallet-container');
        if (walletContainer) {
            walletContainer.insertBefore(alertDiv, walletContainer.firstChild);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    }

    // Public methods for global access
    static openWithdrawalModal() {
        if (window.walletDashboard) {
            window.walletDashboard.openWithdrawalModal();
        }
    }

    static submitWithdrawal() {
        if (window.walletDashboard) {
            window.walletDashboard.submitWithdrawal();
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.walletDashboard = new WalletDashboard();
});

// Global functions for onclick handlers
function openWithdrawalModal() {
    WalletDashboard.openWithdrawalModal();
}

function submitWithdrawal() {
    WalletDashboard.submitWithdrawal();
}
