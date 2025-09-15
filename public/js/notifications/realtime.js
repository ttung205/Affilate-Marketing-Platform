/**
 * Real-time Notifications System
 * Integrates with existing notification bell in header
 */

if (typeof RealtimeNotifications === 'undefined') {
class RealtimeNotifications {
    constructor() {
        this.userId = window.userId || null;
        this.notificationCount = 0;
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        
        this.init();
    }

    init() {
        if (!this.userId) {
            console.warn('User ID not found, notifications disabled');
            return;
        }

        this.setupEventListeners();
        this.loadNotifications();
        this.connectWebSocket();
    }

    setupEventListeners() {
        // Mark all as read button
        const markAllBtn = document.querySelector('.mark-all-read');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', () => this.markAllAsRead());
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const dropdown = document.querySelector('.notification-dropdown');
            if (dropdown && !dropdown.contains(e.target)) {
                this.closeDropdown();
            }
        });
    }

    connectWebSocket() {
        // For now, always use polling since WebSocket is not setup
        console.log('Using polling for notifications (WebSocket not configured)');
        this.startPolling();
        return;

        // Check if Laravel Echo is available
        if (typeof window.Echo === 'undefined') {
            console.warn('Laravel Echo not found, using polling instead');
            this.startPolling();
            return;
        }

        try {
            // Listen for user-specific notifications
            window.Echo.private(`user.${this.userId}`)
                .notification((notification) => {
                    this.handleNewNotification(notification);
                });

            // Listen for general notifications
            window.Echo.channel('notifications')
                .listen('RealTimeNotification', (e) => {
                    if (e.notifiable_id === this.userId) {
                        this.handleNewNotification(e);
                    }
                });

            this.isConnected = true;
            console.log('WebSocket connected for notifications');
        } catch (error) {
            console.error('WebSocket connection failed:', error);
            this.startPolling();
        }
    }

    startPolling() {
        // Fallback to polling if WebSocket fails
        setInterval(() => {
            this.loadNotifications();
        }, 5000); // Poll every 5 seconds
    }

    async loadNotifications() {
        try {
            console.log('Loading notifications...');
            const response = await fetch('/api/notifications');
            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Notifications data:', data);
            
            if (data.notifications) {
                this.updateNotificationList(data.notifications);
                this.updateNotificationCount(data.notifications.filter(n => !n.read_at).length);
            }
        } catch (error) {
            console.error('Failed to load notifications:', error);
        }
    }

    async loadUnreadCount() {
        try {
            const response = await fetch('/api/notifications/unread-count');
            const data = await response.json();
            this.updateNotificationCount(data.count);
        } catch (error) {
            console.error('Failed to load unread count:', error);
        }
    }

    handleNewNotification(notification) {
        // Add to notification list
        this.addNotificationToList(notification);
        
        // Update count
        this.updateNotificationCount(this.notificationCount + 1);
        
        // Show browser notification if permission granted
        this.showBrowserNotification(notification);
        
        // Play sound
        this.playNotificationSound();
    }

    updateNotificationList(notifications) {
        const list = document.querySelector('.notification-list');
        if (!list) return;

        if (notifications.length === 0) {
            list.innerHTML = `
                <div class="notification-item">
                    <div class="notification-icon">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <div class="notification-content">
                        <p>Chưa có thông báo nào</p>
                        <span class="notification-time">Hệ thống sẽ thông báo khi có hoạt động mới</span>
                    </div>
                </div>
            `;
            return;
        }

        list.innerHTML = notifications.map(notification => this.renderNotification(notification)).join('');
    }

    addNotificationToList(notification) {
        const list = document.querySelector('.notification-list');
        if (!list) return;

        // Remove empty state if exists
        const emptyState = list.querySelector('.notification-item p');
        if (emptyState && emptyState.textContent.includes('Chưa có thông báo')) {
            list.innerHTML = '';
        }

        // Add new notification to top
        const notificationElement = document.createElement('div');
        notificationElement.innerHTML = this.renderNotification(notification);
        list.insertBefore(notificationElement.firstElementChild, list.firstChild);
    }

    renderNotification(notification) {
        const isUnread = !notification.read_at;
        const timeAgo = this.getTimeAgo(notification.created_at);
        const title = notification.data?.title || 'Thông báo mới';
        const message = notification.data?.message || '';
        const preview = this.truncateText(message, 80); // Preview 80 ký tự
        
        return `
            <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                <div class="notification-icon" style="background-color: ${this.getNotificationColor(notification.data?.color || 'blue')}">
                    <i class="${notification.data?.icon || 'fas fa-bell'}"></i>
                </div>
                <div class="notification-content">
                    <h6 class="notification-title">${title}</h6>
                    ${preview ? `<p class="notification-preview">${preview}</p>` : ''}
                    <span class="notification-time">${timeAgo}</span>
                </div>
                <div class="notification-actions">
                    ${isUnread ? `<button onclick="realtimeNotifications.markAsRead('${notification.id}')" class="mark-read-btn" title="Đánh dấu đã đọc">
                        <i class="fas fa-check"></i>
                    </button>` : ''}
                    <button onclick="realtimeNotifications.deleteNotification('${notification.id}')" class="delete-btn" title="Xóa">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
    }

    updateNotificationCount(count) {
        this.notificationCount = count;
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    async markAsRead(notificationId) {
        try {
            await fetch(`/api/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            // Update UI in dropdown
            const notificationElement = document.querySelector(`[data-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.classList.remove('unread');
                const markReadBtn = notificationElement.querySelector('.mark-read-btn');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
            }

            // Update UI in modal
            const modalNotificationElement = document.querySelector(`.modal-notification-item[data-id="${notificationId}"]`);
            if (modalNotificationElement) {
                modalNotificationElement.classList.remove('unread');
                const modalMarkReadBtn = modalNotificationElement.querySelector('.mark-read-btn');
                if (modalMarkReadBtn) {
                    modalMarkReadBtn.remove();
                }
            }

            this.updateNotificationCount(Math.max(0, this.notificationCount - 1));
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    }

    async markAllAsRead() {
        try {
            await fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            // Update UI in dropdown
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                const markReadBtn = item.querySelector('.mark-read-btn');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
            });

            // Update UI in modal
            document.querySelectorAll('.modal-notification-item.unread').forEach(item => {
                item.classList.remove('unread');
                const markReadBtn = item.querySelector('.mark-read-btn');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
            });

            this.updateNotificationCount(0);
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
        }
    }

    async deleteNotification(notificationId) {
        try {
            await fetch(`/api/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            // Remove from dropdown UI
            const notificationElement = document.querySelector(`[data-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.remove();
            }

            // Remove from modal UI
            const modalNotificationElement = document.querySelector(`.modal-notification-item[data-id="${notificationId}"]`);
            if (modalNotificationElement) {
                modalNotificationElement.remove();
            }

            // Check if dropdown list is empty
            const list = document.querySelector('.notification-list');
            if (list && list.children.length === 0) {
                this.updateNotificationList([]);
            }

            // Check if modal list is empty
            const modalList = document.querySelector('.notification-modal-list');
            if (modalList && modalList.children.length === 0) {
                this.renderModalNotification([]);
            }
        } catch (error) {
            console.error('Failed to delete notification:', error);
        }
    }

    showBrowserNotification(notification) {
        if (Notification.permission === 'granted') {
            new Notification(notification.data?.title || 'Thông báo mới', {
                body: notification.data?.message || 'Bạn có thông báo mới',
                icon: '/favicon.ico',
                tag: notification.id
            });
        }
    }

    playNotificationSound() {
        // Create audio element for notification sound
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU5k9n1unEiBS13yO/eizEIHWq+8+OWT');
        audio.volume = 0.3;
        audio.play().catch(() => {
            // Ignore errors if audio can't play
        });
    }

    getTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Vừa xong';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} phút trước`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} giờ trước`;
        if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} ngày trước`;
        
        return date.toLocaleDateString('vi-VN');
    }

    getNotificationColor(color) {
        const colors = {
            'blue': '#f0f9ff',
            'green': '#f0fdf4',
            'yellow': '#fefce8',
            'red': '#fef2f2',
            'purple': '#faf5ff',
            'indigo': '#eef2ff',
            'teal': '#f0fdfa',
            'gray': '#f9fafb'
        };
        return colors[color] || colors['blue'];
    }

    closeDropdown() {
        const menu = document.getElementById('notificationMenu');
        if (menu) {
            menu.classList.remove('show');
        }
    }

    truncateText(text, maxLength) {
        if (!text) return '';
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }

    // Tạo popup xem tất cả thông báo
    showAllNotifications() {
        this.createNotificationModal();
    }

    createNotificationModal() {
        // Tạo modal nếu chưa có
        let modal = document.getElementById('notificationModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'notificationModal';
            modal.className = 'notification-modal-overlay';
            modal.innerHTML = `
                <div class="notification-modal">
                    <div class="notification-modal-header">
                        <h3>Tất cả thông báo</h3>
                        <button class="close-modal" onclick="realtimeNotifications.closeNotificationModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="notification-modal-body">
                        <div class="notification-filters">
                            <button class="filter-btn active" data-filter="all">Tất cả</button>
                            <button class="filter-btn" data-filter="unread">Chưa đọc</button>
                            <button class="filter-btn" data-filter="read">Đã đọc</button>
                        </div>
                        <div class="notification-modal-list" id="notificationModalList">
                            <!-- Notifications will be loaded here -->
                        </div>
                    </div>
                    <div class="notification-modal-footer">
                        <button class="btn btn-secondary" onclick="realtimeNotifications.markAllAsRead()">
                            Đánh dấu tất cả đã đọc
                        </button>
                        <button class="btn btn-primary" onclick="realtimeNotifications.closeNotificationModal()">
                            Đóng
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // Load tất cả thông báo
        this.loadAllNotifications();
        
        // Hiển thị modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Setup filter buttons
        this.setupNotificationFilters();
    }

    async loadAllNotifications() {
        try {
            const response = await fetch('/api/notifications?all=true');
            const data = await response.json();
            
            const modalList = document.getElementById('notificationModalList');
            if (modalList) {
                if (data.notifications && data.notifications.length > 0) {
                    modalList.innerHTML = data.notifications.map(notification => this.renderModalNotification(notification)).join('');
                } else {
                    modalList.innerHTML = `
                        <div class="no-notifications">
                            <i class="fas fa-bell-slash"></i>
                            <p>Chưa có thông báo nào</p>
                        </div>
                    `;
                }
            }
        } catch (error) {
            console.error('Failed to load all notifications:', error);
        }
    }

    renderModalNotification(notification) {
        const isUnread = !notification.read_at;
        const timeAgo = this.getTimeAgo(notification.created_at);
        const title = notification.data?.title || 'Thông báo mới';
        const message = notification.data?.message || '';
        
        return `
            <div class="modal-notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                <div class="modal-notification-icon" style="background-color: ${this.getNotificationColor(notification.data?.color || 'blue')}">
                    <i class="${notification.data?.icon || 'fas fa-bell'}"></i>
                </div>
                <div class="modal-notification-content">
                    <h6 class="modal-notification-title">${title}</h6>
                    <p class="modal-notification-message">${message}</p>
                    <div class="modal-notification-meta">
                        <span class="modal-notification-time">${timeAgo}</span>
                        ${isUnread ? '<span class="unread-badge">Chưa đọc</span>' : ''}
                    </div>
                </div>
                <div class="modal-notification-actions">
                    ${isUnread ? `<button onclick="realtimeNotifications.markAsRead('${notification.id}')" class="mark-read-btn" title="Đánh dấu đã đọc">
                        <i class="fas fa-check"></i>
                    </button>` : ''}
                    <button onclick="realtimeNotifications.deleteNotification('${notification.id}')" class="delete-btn" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }

    setupNotificationFilters() {
        const filterBtns = document.querySelectorAll('.filter-btn');
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active class from all buttons
                filterBtns.forEach(b => b.classList.remove('active'));
                // Add active class to clicked button
                btn.classList.add('active');
                
                // Filter notifications
                const filter = btn.dataset.filter;
                this.filterNotifications(filter);
            });
        });
    }

    filterNotifications(filter) {
        const items = document.querySelectorAll('.modal-notification-item');
        items.forEach(item => {
            const isUnread = item.classList.contains('unread');
            let show = true;
            
            if (filter === 'unread' && !isUnread) show = false;
            if (filter === 'read' && isUnread) show = false;
            
            item.style.display = show ? 'flex' : 'none';
        });
    }

    closeNotificationModal() {
        const modal = document.getElementById('notificationModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.realtimeNotifications = new RealtimeNotifications();
});

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
}
