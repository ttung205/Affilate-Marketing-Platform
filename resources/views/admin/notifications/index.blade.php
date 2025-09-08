@extends('components.dashboard.layout')

@section('title', 'Quản lý Thông báo')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/notifications.css') }}">
@endpush

@section('content')
<div class="notifications-container">
    <!-- Header Section -->
    <div class="notifications-header">
        <div class="notifications-header-left">
            <h1 class="notifications-title">Quản lý Thông báo</h1>
            <p class="notifications-subtitle">Gửi và quản lý thông báo cho người dùng</p>
        </div>
        <div class="notifications-header-right">
            <button class="notifications-btn notifications-btn-primary" onclick="loadStats()">
                <i class="fas fa-sync-alt"></i>
                <span>Làm mới</span>
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="notifications-stats-grid">
        <div class="notifications-stat-card notifications-stat-primary">
            <div class="notifications-stat-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="notifications-stat-content">
                <h3 id="totalNotifications">0</h3>
                <p>Tổng thông báo</p>
            </div>
        </div>

        <div class="notifications-stat-card notifications-stat-warning">
            <div class="notifications-stat-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="notifications-stat-content">
                <h3 id="unreadNotifications">0</h3>
                <p>Chưa đọc</p>
            </div>
        </div>

        <div class="notifications-stat-card notifications-stat-success">
            <div class="notifications-stat-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="notifications-stat-content">
                <h3 id="todayNotifications">0</h3>
                <p>Hôm nay</p>
            </div>
        </div>

        <div class="notifications-stat-card notifications-stat-info">
            <div class="notifications-stat-icon">
                <i class="fas fa-calendar-week"></i>
            </div>
            <div class="notifications-stat-content">
                <h3 id="weekNotifications">0</h3>
                <p>Tuần này</p>
            </div>
        </div>
    </div>

    <!-- Send Notification Forms -->
    <div class="notifications-forms-grid">
        <!-- Send to All Users -->
        <div class="notifications-form-card">
            <div class="form-header">
                <div class="form-header-icon">
                    <i class="fas fa-broadcast-tower"></i>
                </div>
                <div class="form-header-content">
                    <h3 class="form-title">Gửi cho tất cả</h3>
                    <p class="form-subtitle">Gửi thông báo cho tất cả người dùng ({{ $userCounts['all'] }} người)</p>
                </div>
            </div>
            
            <div class="form-body">
                <form id="sendToAllForm">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Tiêu đề <span class="required">*</span></label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nội dung <span class="required">*</span></label>
                        <textarea class="form-control" name="message" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Icon</label>
                                <select class="form-select" name="icon">
                                    <option value="fas fa-bell">🔔 Thông báo</option>
                                    <option value="fas fa-info-circle">ℹ️ Thông tin</option>
                                    <option value="fas fa-exclamation-triangle">⚠️ Cảnh báo</option>
                                    <option value="fas fa-check-circle">✅ Thành công</option>
                                    <option value="fas fa-times-circle">❌ Lỗi</option>
                                    <option value="fas fa-gift">🎁 Quà tặng</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Màu sắc</label>
                                <select class="form-select" name="color">
                                    <option value="blue">🔵 Xanh dương</option>
                                    <option value="green">🟢 Xanh lá</option>
                                    <option value="yellow">🟡 Vàng</option>
                                    <option value="red">🔴 Đỏ</option>
                                    <option value="purple">🟣 Tím</option>
                                    <option value="indigo">🔵 Indigo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Loại thông báo <span class="required">*</span></label>
                        <select class="form-select" name="type" required>
                            @foreach($templates as $template)
                                <option value="{{ $template->type }}">{{ $template->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="notifications-btn notifications-btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            <span>Gửi cho tất cả</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Send to Specific Role -->
        <div class="notifications-form-card">
            <div class="form-header">
                <div class="form-header-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="form-header-content">
                    <h3 class="form-title">Gửi theo role</h3>
                    <p class="form-subtitle">Gửi thông báo cho role cụ thể</p>
                </div>
            </div>
            
            <div class="form-body">
                <form id="sendToRoleForm">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Chọn Role <span class="required">*</span></label>
                        <select class="form-select" name="role" id="roleSelect" required>
                            <option value="">-- Chọn role --</option>
                            <option value="admin">👑 Admin ({{ $userCounts['admin'] }} người)</option>
                            <option value="shop">🏪 Shop ({{ $userCounts['shop'] }} người)</option>
                            <option value="publisher">📢 Publisher ({{ $userCounts['publisher'] }} người)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tiêu đề <span class="required">*</span></label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nội dung <span class="required">*</span></label>
                        <textarea class="form-control" name="message" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Icon</label>
                                <select class="form-select" name="icon">
                                    <option value="fas fa-bell">🔔 Thông báo</option>
                                    <option value="fas fa-info-circle">ℹ️ Thông tin</option>
                                    <option value="fas fa-exclamation-triangle">⚠️ Cảnh báo</option>
                                    <option value="fas fa-check-circle">✅ Thành công</option>
                                    <option value="fas fa-times-circle">❌ Lỗi</option>
                                    <option value="fas fa-gift">🎁 Quà tặng</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Màu sắc</label>
                                <select class="form-select" name="color">
                                    <option value="blue">🔵 Xanh dương</option>
                                    <option value="green">🟢 Xanh lá</option>
                                    <option value="yellow">🟡 Vàng</option>
                                    <option value="red">🔴 Đỏ</option>
                                    <option value="purple">🟣 Tím</option>
                                    <option value="indigo">🔵 Indigo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Loại thông báo <span class="required">*</span></label>
                        <select class="form-select" name="type" required>
                            @foreach($templates as $template)
                                <option value="{{ $template->type }}">{{ $template->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="notifications-btn notifications-btn-success">
                            <i class="fas fa-paper-plane"></i>
                            <span>Gửi theo role</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Send to Specific User -->
    <div class="notifications-form-card notifications-form-full">
        <div class="form-header">
            <div class="form-header-icon">
                <i class="fas fa-user"></i>
            </div>
            <div class="form-header-content">
                <h3 class="form-title">Gửi cho người dùng cụ thể</h3>
                <p class="form-subtitle">Gửi thông báo cho một người dùng cụ thể</p>
            </div>
        </div>
        
        <div class="form-body">
            <form id="sendToUserForm">
                @csrf
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Chọn người dùng <span class="required">*</span></label>
                            <select class="form-select" name="user_id" id="userSelect" required>
                                <option value="">-- Chọn người dùng --</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Lọc theo role</label>
                            <select class="form-select" id="filterRole">
                                <option value="">-- Tất cả --</option>
                                <option value="admin">👑 Admin</option>
                                <option value="shop">🏪 Shop</option>
                                <option value="publisher">📢 Publisher</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tiêu đề <span class="required">*</span></label>
                    <input type="text" class="form-control" name="title" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nội dung <span class="required">*</span></label>
                    <textarea class="form-control" name="message" rows="3" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Icon</label>
                            <select class="form-select" name="icon">
                                <option value="fas fa-bell">🔔 Thông báo</option>
                                <option value="fas fa-info-circle">ℹ️ Thông tin</option>
                                <option value="fas fa-exclamation-triangle">⚠️ Cảnh báo</option>
                                <option value="fas fa-check-circle">✅ Thành công</option>
                                <option value="fas fa-times-circle">❌ Lỗi</option>
                                <option value="fas fa-gift">🎁 Quà tặng</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Màu sắc</label>
                            <select class="form-select" name="color">
                                <option value="blue">🔵 Xanh dương</option>
                                <option value="green">🟢 Xanh lá</option>
                                <option value="yellow">🟡 Vàng</option>
                                <option value="red">🔴 Đỏ</option>
                                <option value="purple">🟣 Tím</option>
                                <option value="indigo">🔵 Indigo</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Loại thông báo <span class="required">*</span></label>
                            <select class="form-select" name="type" required>
                                @foreach($templates as $template)
                                    <option value="{{ $template->type }}">{{ $template->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="notifications-btn notifications-btn-info">
                        <i class="fas fa-paper-plane"></i>
                        <span>Gửi cho người dùng</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Popup -->
<div id="successPopup" class="popup-overlay">
    <div class="popup-content">
        <div class="popup-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 class="popup-title">Thành công!</h3>
        <p class="popup-message" id="successMessage"></p>
        <button class="popup-button" onclick="closeSuccessPopup()">
            <i class="fas fa-check"></i>
            <span>Đóng</span>
        </button>
    </div>
</div>

<!-- Error Popup -->
<div id="errorPopup" class="popup-overlay">
    <div class="popup-content error-popup">
        <div class="popup-icon error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="popup-title">Lỗi!</h3>
        <p class="popup-message" id="errorMessage"></p>
        <button class="popup-button error-button" onclick="closeErrorPopup()">
            <i class="fas fa-times"></i>
            <span>Đóng</span>
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadUsers(); // Load users khi trang load
    
    // Ẩn modal mặc định
    const successModal = document.getElementById('successModal');
    if (successModal) {
        successModal.style.display = 'none';
        successModal.classList.remove('show');
    }
    
    // Role filter change
    document.getElementById('filterRole').addEventListener('change', function() {
        loadUsers(this.value);
    });
    
    // Send to all form
    document.getElementById('sendToAllForm').addEventListener('submit', function(e) {
        console.log('Form submit event triggered!');
        e.preventDefault();
        console.log('About to call sendNotification...');
        sendNotification('all', this);
    });
    
    // Send to role form
    document.getElementById('sendToRoleForm').addEventListener('submit', function(e) {
        console.log('Role form submit event triggered!');
        e.preventDefault();
        sendNotification('role', this);
    });
    
    // Send to user form
    document.getElementById('sendToUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendNotification('user', this);
    });
});

function loadStats() {
    fetch('/admin/notifications/stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalNotifications').textContent = data.total_notifications;
            document.getElementById('unreadNotifications').textContent = data.unread_notifications;
            document.getElementById('todayNotifications').textContent = data.notifications_today;
            document.getElementById('weekNotifications').textContent = data.notifications_this_week;
        })
        .catch(error => console.error('Error loading stats:', error));
}

function loadUsers(role = '') {
    console.log('Loading users for role:', role);
    
    fetch(`/admin/notifications/users?role=${role}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
        .then(response => {
            console.log('Users response status:', response.status);
            return response.json();
        })
        .then(users => {
            console.log('Users data:', users);
            const select = document.getElementById('userSelect');
            select.innerHTML = '<option value="">-- Chọn người dùng --</option>';
            
            if (users && users.length > 0) {
                users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = `${user.name} (${user.email}) - ${user.role}`;
                    select.appendChild(option);
                });
                console.log(`Loaded ${users.length} users`);
            } else {
                console.log('No users found');
                const option = document.createElement('option');
                option.value = '';
                option.textContent = '-- Không có người dùng --';
                select.appendChild(option);
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            const select = document.getElementById('userSelect');
            select.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
        });
}

function sendNotification(type, form) {
    console.log('=== SEND NOTIFICATION DEBUG ===');
    console.log('Sending notification type:', type);
    console.log('Form data:', new FormData(form));
    
    const formData = new FormData(form);
    const url = type === 'all' ? '/admin/notifications/send-all' :
                type === 'role' ? '/admin/notifications/send-role' :
                '/admin/notifications/send-user';
    
    console.log('Sending to URL:', url);
    console.log('Form data entries:');
    for (let [key, value] of formData.entries()) {
        console.log(key, ':', value);
    }
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Hiển thị thông báo thành công với animation
            showSuccessModal(data.message);
            form.reset();
            loadStats(); // Reload stats
        } else {
            showErrorModal(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorModal('Có lỗi xảy ra khi gửi thông báo');
    });
}

function showSuccessModal(message) {
    console.log('=== SHOW SUCCESS POPUP ===');
    console.log('Message:', message);
    
    const popup = document.getElementById('successPopup');
    const messageElement = document.getElementById('successMessage');
    
    if (!popup || !messageElement) {
        console.error('Popup elements not found!');
        return;
    }
    
    // Cập nhật nội dung thông báo
    messageElement.textContent = message;
    
    // Hiển thị popup
    popup.classList.add('show');
    
    // Tự động đóng sau 3 giây
    setTimeout(() => {
        closeSuccessPopup();
    }, 3000);
}

function closeSuccessPopup() {
    const popup = document.getElementById('successPopup');
    if (popup) {
        popup.classList.remove('show');
    }
}

function showErrorModal(message) {
    console.log('=== SHOW ERROR POPUP ===');
    console.log('Message:', message);
    
    const popup = document.getElementById('errorPopup');
    const messageElement = document.getElementById('errorMessage');
    
    if (!popup || !messageElement) {
        console.error('Error popup elements not found!');
        return;
    }
    
    // Cập nhật nội dung thông báo
    messageElement.textContent = message;
    
    // Hiển thị popup
    popup.classList.add('show');
}

function closeErrorPopup() {
    const popup = document.getElementById('errorPopup');
    if (popup) {
        popup.classList.remove('show');
    }
}
</script>
@endpush