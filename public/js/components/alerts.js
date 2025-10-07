// Modern Alert System JavaScript
class AlertSystem {
    constructor() {
        this.container = document.getElementById('alertContainer');
        this.alerts = [];
        this.init();
    }

    init() {
        // Auto-close alerts after 5 seconds
        this.setupAutoClose();
        
        // Setup click outside to close
        this.setupClickOutside();
        
        // Setup keyboard shortcuts
        this.setupKeyboardShortcuts();
        
        // Setup swipe gestures for mobile
        this.setupSwipeGestures();
    }

    setupAutoClose() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert && !alert.classList.contains('closing')) {
                    this.closeAlert(alert);
                }
            }, 5000);
        });
    }

    setupClickOutside() {
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.alert') && !e.target.closest('.alert-close')) {
                this.closeAllAlerts();
            }
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllAlerts();
            }
        });
    }

    setupSwipeGestures() {
        let startX = 0;
        let currentX = 0;

        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
            });

            alert.addEventListener('touchmove', (e) => {
                currentX = e.touches[0].clientX;
                const diffX = startX - currentX;
                
                if (diffX > 50) { // Swipe left
                    alert.style.transform = `translateX(-${diffX}px)`;
                }
            });

            alert.addEventListener('touchend', (e) => {
                const diffX = startX - currentX;
                if (diffX > 100) { // Swipe left enough
                    this.closeAlert(alert);
                } else {
                    alert.style.transform = '';
                }
            });
        });
    }

    closeAlert(alert) {
        if (!alert) return;
        
        alert.classList.add('closing');
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 300);
    }

    closeAllAlerts() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => this.closeAlert(alert));
    }

    // Static method to show new alerts
    static show(type, title, message, duration = 5000) {
        const alertHtml = `
            <div class="alert alert-${type}" data-alert="${type}">
                <div class="alert-icon">
                    <i class="fas fa-${AlertSystem.getIcon(type)}"></i>
                </div>
                <div class="alert-content">
                    <div class="alert-title">${title}</div>
                    <div class="alert-message">${message}</div>
                </div>
                <button class="alert-close" onclick="AlertSystem.closeAlert(this.parentElement)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="alert-progress"></div>
            </div>
        `;

        const container = document.getElementById('alertContainer') || 
                        document.createElement('div');
        
        if (!document.getElementById('alertContainer')) {
            container.id = 'alertContainer';
            container.className = 'alert-container';
            document.body.appendChild(container);
        }

        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        const newAlert = container.firstElementChild;
        
        // Auto-close
        setTimeout(() => {
            if (newAlert && !newAlert.classList.contains('closing')) {
                AlertSystem.closeAlert(newAlert);
            }
        }, duration);

        return newAlert;
    }

    static getIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    static closeAlert(alert) {
        if (!alert) return;
        
        alert.classList.add('closing');
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 300);
    }
}

// Initialize Alert System
document.addEventListener('DOMContentLoaded', () => {
    new AlertSystem();
});

// Global functions for easy access
window.showAlert = AlertSystem.show;
window.closeAlert = AlertSystem.closeAlert;

// Compatibility functions for Toast.js (to maintain backward compatibility)
window.showSuccess = function(message, title = 'Thành công!') {
    return AlertSystem.show('success', title, message);
};

window.showError = function(message, title = 'Lỗi!') {
    return AlertSystem.show('error', title, message);
};

window.showWarning = function(message, title = 'Cảnh báo!') {
    return AlertSystem.show('warning', title, message);
};

window.showInfo = function(message, title = 'Thông tin!') {
    return AlertSystem.show('info', title, message);
};

window.showToast = function(message, type = 'info', title = null, duration = 5000) {
    const titles = {
        success: 'Thành công!',
        error: 'Lỗi!',
        warning: 'Cảnh báo!',
        info: 'Thông tin!'
    };
    return AlertSystem.show(type, title || titles[type] || titles.info, message, duration);
};

// Example usage:
// showAlert('success', 'Thành công!', 'Sản phẩm đã được tạo thành công!');
// showAlert('error', 'Lỗi!', 'Không thể tạo sản phẩm!');
// showAlert('warning', 'Cảnh báo!', 'Vui lòng kiểm tra lại thông tin!');
// showAlert('info', 'Thông tin!', 'Đang xử lý yêu cầu của bạn!');
// 
// Or use compatibility functions:
// showSuccess('Tạo thành công!');
// showError('Có lỗi xảy ra!');
// showToast('Đã copy!', 'success');