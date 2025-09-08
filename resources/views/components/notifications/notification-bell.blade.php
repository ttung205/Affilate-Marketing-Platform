<div class="notification-bell" id="notificationBell">
    <button class="notification-btn" onclick="toggleNotifications()">
        <i class="fas fa-bell"></i>
        <span class="notification-count" id="notificationCount">0</span>
    </button>
    
    <div class="notification-dropdown" id="notificationDropdown">
        <div class="notification-header">
            <h3>Thông báo</h3>
            <button class="mark-all-read" onclick="markAllAsRead()">
                Đánh dấu tất cả đã đọc
            </button>
        </div>
        
        <div class="notification-list" id="notificationList">
            <!-- Notifications will be loaded here -->
        </div>
        
        <div class="notification-footer">
            <a href="/notifications" class="view-all">Xem tất cả</a>
        </div>
    </div>
</div>

<style>
.notification-bell {
    position: relative;
    display: inline-block;
}

.notification-btn {
    position: relative;
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #6c757d;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.notification-btn:hover {
    background-color: #f8f9fa;
    color: #007bff;
}

.notification-count {
    position: absolute;
    top: 0;
    right: 0;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 350px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    display: none;
    max-height: 400px;
    overflow-y: auto;
}

.notification-dropdown.show {
    display: block;
}

.notification-header {
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-header h3 {
    margin: 0;
    font-size: 1.1rem;
    color: #495057;
}

.mark-all-read {
    background: none;
    border: none;
    color: #007bff;
    cursor: pointer;
    font-size: 0.9rem;
}

.notification-list {
    max-height: 300px;
    overflow-y: auto;
}

.notification-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e3f2fd;
    border-left: 3px solid #2196f3;
}

.notification-item-content {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.notification-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.notification-text {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    color: #495057;
    margin: 0 0 4px 0;
    font-size: 0.9rem;
}

.notification-message {
    color: #6c757d;
    margin: 0;
    font-size: 0.85rem;
    line-height: 1.4;
}

.notification-time {
    color: #adb5bd;
    font-size: 0.75rem;
    margin-top: 4px;
}

.notification-footer {
    padding: 15px;
    text-align: center;
    border-top: 1px solid #dee2e6;
}

.view-all {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
}

.view-all:hover {
    text-decoration: underline;
}

/* Empty state */
.notification-empty {
    padding: 40px 20px;
    text-align: center;
    color: #6c757d;
}

.notification-empty i {
    font-size: 2rem;
    margin-bottom: 10px;
    opacity: 0.5;
}
</style>
