// Toast Notification System
class ToastNotification {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        this.createContainer();
        this.addStyles();
    }

    createContainer() {
        this.container = document.createElement('div');
        this.container.id = 'toast-container';
        this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        `;
        document.body.appendChild(this.container);
    }

    addStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .toast {
                min-width: 300px;
                max-width: 500px;
                padding: 16px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                display: flex;
                align-items: center;
                gap: 12px;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 14px;
                line-height: 1.4;
                transform: translateX(100%);
                opacity: 0;
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }

            .toast.show {
                transform: translateX(0);
                opacity: 1;
            }

            .toast.success {
                background: #d1fae5;
                border: 1px solid #a7f3d0;
                color: #065f46;
            }

            .toast.error {
                background: #fee2e2;
                border: 1px solid #fecaca;
                color: #991b1b;
            }

            .toast.warning {
                background: #fef3c7;
                border: 1px solid #fde68a;
                color: #92400e;
            }

            .toast.info {
                background: #dbeafe;
                border: 1px solid #93c5fd;
                color: #1e40af;
            }

            .toast-icon {
                font-size: 20px;
                flex-shrink: 0;
            }

            .toast-content {
                flex: 1;
            }

            .toast-title {
                font-weight: 600;
                margin: 0 0 4px 0;
            }

            .toast-message {
                margin: 0;
                opacity: 0.9;
            }

            .toast-close {
                background: none;
                border: none;
                color: inherit;
                font-size: 18px;
                cursor: pointer;
                padding: 0;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
                transition: background-color 0.2s ease;
                flex-shrink: 0;
            }

            .toast-close:hover {
                background: rgba(0, 0, 0, 0.1);
            }

            .toast-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background: rgba(0, 0, 0, 0.2);
                border-radius: 0 0 8px 8px;
                animation: progress 5s linear forwards;
            }

            @keyframes progress {
                from { width: 100%; }
                to { width: 0%; }
            }

            @media (max-width: 768px) {
                .toast {
                    min-width: 280px;
                    max-width: calc(100vw - 40px);
                }
            }
        `;
        document.head.appendChild(style);
    }

    show(message, type = 'info', title = null, duration = 5000) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;

        const icon = this.getIcon(type);
        const closeButton = this.createCloseButton(toast);

        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                ${title ? `<div class="toast-title">${title}</div>` : ''}
                <div class="toast-message">${message}</div>
            </div>
        `;

        toast.appendChild(closeButton);
        toast.appendChild(this.createProgressBar());

        this.container.appendChild(toast);

        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 100);

        // Auto remove
        if (duration > 0) {
            setTimeout(() => this.remove(toast), duration);
        }

        return toast;
    }

    getIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    }

    createCloseButton(toast) {
        const button = document.createElement('button');
        button.className = 'toast-close';
        button.innerHTML = '×';
        button.onclick = () => this.remove(toast);
        return button;
    }

    createProgressBar() {
        const progress = document.createElement('div');
        progress.className = 'toast-progress';
        return progress;
    }

    remove(toast) {
        if (toast && toast.parentNode) {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }
    }

    success(message, title = 'Thành công') {
        return this.show(message, 'success', title);
    }

    error(message, title = 'Lỗi') {
        return this.show(message, 'error', title);
    }

    warning(message, title = 'Cảnh báo') {
        return this.show(message, 'warning', title);
    }

    info(message, title = 'Thông tin') {
        return this.show(message, 'info', title);
    }

    // Static methods for easy access
    static success(message, title = 'Thành công') {
        if (!window.toastNotification) {
            window.toastNotification = new ToastNotification();
        }
        return window.toastNotification.success(message, title);
    }

    static error(message, title = 'Lỗi') {
        if (!window.toastNotification) {
            window.toastNotification = new ToastNotification();
        }
        return window.toastNotification.error(message, title);
    }

    static warning(message, title = 'Cảnh báo') {
        if (!window.toastNotification) {
            window.toastNotification = new ToastNotification();
        }
        return window.toastNotification.warning(message, title);
    }

    static info(message, title = 'Thông tin') {
        if (!window.toastNotification) {
            window.toastNotification = new ToastNotification();
        }
        return window.toastNotification.info(message, title);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.toastNotification = new ToastNotification();
});

// Global functions for easy access
function showToast(message, type = 'info', title = null, duration = 5000) {
    if (window.toastNotification) {
        return window.toastNotification.show(message, type, title, duration);
    }
}

function showSuccess(message, title = 'Thành công') {
    return ToastNotification.success(message, title);
}

function showError(message, title = 'Lỗi') {
    return ToastNotification.error(message, title);
}

function showWarning(message, title = 'Cảnh báo') {
    return ToastNotification.warning(message, title);
}

function showInfo(message, title = 'Thông tin') {
    return ToastNotification.info(message, title);
}
