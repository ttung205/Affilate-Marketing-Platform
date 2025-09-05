// Chat Sidebar JavaScript - Unread count and real-time updates

document.addEventListener('DOMContentLoaded', function() {
    const unreadCountElement = document.getElementById('unread-count');
    
    // Load initial unread count
    loadUnreadCount();
    
    // Update unread count every 30 seconds
    setInterval(loadUnreadCount, 30000);
    
    // Listen for real-time updates if Echo is available
    if (window.Echo && typeof userId !== 'undefined') {
        window.Echo.private(`App.Models.User.${userId}`)
            .listen('.message.sent', (e) => {
                // Update unread count when new message received
                loadUnreadCount();
            });
    }
    
    function loadUnreadCount() {
        fetch('/chat/messages/unread-count', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.unread_count > 0) {
                unreadCountElement.textContent = data.unread_count;
                unreadCountElement.style.display = 'inline-block';
            } else {
                unreadCountElement.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading unread count:', error);
        });
    }
});

// Add CSS for badge
const style = document.createElement('style');
style.textContent = `
.badge {
    display: inline-block;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: 600;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 10px;
    margin-left: 6px;
}

.badge-danger {
    color: #fff;
    background-color: #dc3545;
}

.sidebar-menu a {
    position: relative;
}
`;
document.head.appendChild(style);
