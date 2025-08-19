// Confirm Popup Component
class ConfirmPopup {
    constructor() {
        this.overlay = null;
        this.popup = null;
        this.onConfirm = null;
        this.init();
    }

    init() {
        // Create overlay
        this.overlay = document.createElement('div');
        this.overlay.className = 'confirm-popup-overlay';
        this.overlay.innerHTML = `
            <div class="confirm-popup">
                <div class="confirm-popup-header">
                    <div class="confirm-popup-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="confirm-popup-title">Xác nhận</h3>
                </div>
                <div class="confirm-popup-body">
                    <p class="confirm-popup-message">Bạn có chắc chắn muốn thực hiện hành động này?</p>
                </div>
                <div class="confirm-popup-actions">
                    <button class="confirm-popup-btn confirm-popup-btn-cancel">
                        <i class="fas fa-times"></i>
                        Hủy bỏ
                    </button>
                    <button class="confirm-popup-btn confirm-popup-btn-confirm">
                        <i class="fas fa-check"></i>
                        Xác nhận
                    </button>
                </div>
            </div>
        `;

        // Add event listeners
        this.overlay.querySelector('.confirm-popup-btn-cancel').addEventListener('click', () => {
            this.hide();
        });

        this.overlay.querySelector('.confirm-popup-btn-confirm').addEventListener('click', () => {
            if (this.onConfirm) {
                this.onConfirm();
            }
            this.hide();
        });

        // Close on overlay click
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) {
                this.hide();
            }
        });

        // Add to body
        document.body.appendChild(this.overlay);
    }

    show(options = {}) {
        const {
            title = 'Xác nhận',
            message = 'Bạn có chắc chắn muốn thực hiện hành động này?',
            type = 'danger', // 'danger' or 'warning'
            confirmText = 'Xác nhận',
            cancelText = 'Hủy bỏ',
            onConfirm = null
        } = options;

        // Update content
        this.overlay.querySelector('.confirm-popup-title').textContent = title;
        this.overlay.querySelector('.confirm-popup-message').textContent = message;
        this.overlay.querySelector('.confirm-popup-btn-confirm').textContent = confirmText;
        this.overlay.querySelector('.confirm-popup-btn-cancel').textContent = cancelText;

        // Update icon and button styles
        const icon = this.overlay.querySelector('.confirm-popup-icon');
        const confirmBtn = this.overlay.querySelector('.confirm-popup-btn-confirm');
        
        icon.className = `confirm-popup-icon ${type}`;
        confirmBtn.className = `confirm-popup-btn confirm-popup-btn-confirm ${type}`;

        // Update icon
        if (type === 'warning') {
            icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        } else if (type === 'success') {
            icon.innerHTML = '<i class="fas fa-check"></i>';
        } else if (type === 'info') {
            icon.innerHTML = '<i class="fas fa-info-circle"></i>';
        } else {
            icon.innerHTML = '<i class="fas fa-trash"></i>';
        }

        // Set callback
        this.onConfirm = onConfirm;

        // Show popup
        this.overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    hide() {
        this.overlay.classList.remove('show');
        document.body.style.overflow = '';
        this.onConfirm = null;
    }

    destroy() {
        if (this.overlay && this.overlay.parentNode) {
            this.overlay.parentNode.removeChild(this.overlay);
        }
    }
}

// Global instance
window.confirmPopup = new ConfirmPopup();

// Helper functions
window.showConfirmPopup = (options) => {
    window.confirmPopup.show(options);
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (!window.confirmPopup) {
        window.confirmPopup = new ConfirmPopup();
    }
});
