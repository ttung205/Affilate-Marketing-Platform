// Chat Real-time JavaScript
let currentConversationId = null;
let currentUserId = null;
let currentEchoChannel = null;

// Initialize chat when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    currentUserId = window.userId;
    
    // Check Echo connection
    if (typeof window.Echo !== 'undefined') {
        console.log('✅ Echo is available');
    } else {
        console.error('❌ Echo is not available!');
    }
    
    // Load first conversation if exists
    const firstConversation = document.querySelector('.conversation-item.active');
    if (firstConversation) {
        currentConversationId = firstConversation.dataset.conversationId;
        console.log('📱 Loading conversation:', currentConversationId);
        loadMessages();
        setupMessageForm();
        setupEchoListeners();
    }
    
    // Load users for new chat modal
    loadUsersForNewChat();
});

function loadConversation(conversationId) {
    // Update active conversation
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-conversation-id="${conversationId}"]`).classList.add('active');
    
    // Update current conversation
    currentConversationId = conversationId;
    
    // Load conversation details and messages
    loadConversationDetails(conversationId);
    loadMessages();
    
    // Setup form and echo listeners (always refresh)
    setupMessageForm();
    setupEchoListeners();
}

function loadConversationDetails(conversationId) {
    fetch(`/chat/${conversationId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.otherParticipant) {
            const otherUser = data.otherParticipant;
            
            // Update chat header
            document.getElementById('participant-name').textContent = otherUser.name;
            document.getElementById('participant-email').textContent = otherUser.email;
            
            // Update avatar
            const avatarElement = document.querySelector('.chat-header .avatar-circle');
            avatarElement.textContent = otherUser.name.charAt(0).toUpperCase();
            avatarElement.className = `avatar-circle ${otherUser.role}`;
            
            // Update role badge
            const roleBadge = document.querySelector('.role-badge');
            roleBadge.textContent = otherUser.role === 'shop' ? 'Shop' : 'Publisher';
            roleBadge.className = `role-badge ${otherUser.role}`;
        }
    })
    .catch(error => {
        console.error('Error loading conversation details:', error);
    });
}

function loadMessages() {
    if (!currentConversationId) return;
    
    const messagesList = document.getElementById('messages-list');
    messagesList.innerHTML = '<div class="loading-messages"><i class="fas fa-spinner fa-spin"></i><p>Đang tải tin nhắn...</p></div>';
    
    fetch(`/chat/conversations/${currentConversationId}/messages`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMessages(data.messages);
                setTimeout(() => {
                    scrollToBottom();
                }, 100);
                markMessagesAsRead();
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
            messagesList.innerHTML = '<div class="error-messages"><p>Không thể tải tin nhắn</p></div>';
        });
}

function displayMessages(messages) {
    const messagesList = document.getElementById('messages-list');
    messagesList.innerHTML = '';
    messages.forEach(message => {
        appendMessage(message);
    });
}

function appendMessage(message) {
    const messagesList = document.getElementById('messages-list');
    const messageDiv = document.createElement('div');
    const isOwn = message.user_id === currentUserId;
    messageDiv.className = `message ${isOwn ? 'own' : 'other'}`;
    messageDiv.dataset.messageId = message.id;
    messageDiv.dataset.userId = message.user_id;
    
    const time = new Date(message.created_at).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    
    let senderName = '';
    if (!isOwn) {
        senderName = message.user ? message.user.name : 'Unknown';
    }
    
    messageDiv.innerHTML = `
        <div class="message-content">
            ${!isOwn ? `<div class="message-sender">${senderName}</div>` : ''}
            <p>${escapeHtml(message.message)}</p>
            <div class="message-meta">
                <span class="message-time">${time}</span>
                ${isOwn && !message.is_read ? '<i class="fas fa-check message-status"></i>' : ''}
                ${isOwn && message.is_read ? '<i class="fas fa-check-double message-status read"></i>' : ''}
            </div>
        </div>
    `;
    
    messagesList.appendChild(messageDiv);
    
    // Auto scroll to bottom after adding message
    setTimeout(() => {
        scrollToBottom();
    }, 50);
}

function setupMessageForm() {
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    
    // Remove existing listeners to prevent duplicates
    const newForm = messageForm.cloneNode(true);
    messageForm.parentNode.replaceChild(newForm, messageForm);
    
    const form = document.getElementById('message-form');
    const input = document.getElementById('message-input');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    
    input.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
}

function sendMessage() {
    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();
    
    if (!message || !currentConversationId) return;
    
    // Disable input while sending
    messageInput.disabled = true;
    
    fetch('/chat/messages', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            conversation_id: currentConversationId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            messageInput.style.height = 'auto';
            appendMessage(data.message);
            // scrollToBottom() đã được gọi trong appendMessage()
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
    })
    .finally(() => {
        messageInput.disabled = false;
        messageInput.focus();
    });
}

function setupEchoListeners() {
    if (typeof window.Echo !== 'undefined' && currentConversationId) {
        console.log('🔗 Setting up Echo listeners for conversation:', currentConversationId);
        
        // Leave previous channel if exists
        if (currentEchoChannel) {
            console.log('🚪 Leaving previous channel:', currentEchoChannel);
            window.Echo.leave(`conversation.${currentEchoChannel}`);
        }
        
        // Join new channel
        currentEchoChannel = currentConversationId;
        const channel = window.Echo.private(`conversation.${currentConversationId}`);
        
        console.log('📡 Joining channel:', `conversation.${currentConversationId}`);
        
        channel.listen('.message.sent', (data) => {
            console.log('📨 Received real-time message:', data.message.message);
            
            if (data.message.user_id !== currentUserId) {
                console.log('✅ Adding message from other user');
                appendMessage(data.message);
                scrollToBottom();
                markMessagesAsRead();
            } else {
                console.log('⏭️ Ignoring own message');
            }
        });
        
        // Test connection
        channel.subscribed(() => {
            console.log('✅ Successfully subscribed to channel');
        });
        
        channel.error((error) => {
            console.error('❌ Channel error:', error);
        });
    } else {
        console.error('❌ Cannot setup Echo listeners - Echo:', typeof window.Echo, 'Conversation ID:', currentConversationId);
    }
}

function scrollToBottom() {
    const messagesContainer = document.getElementById('messages-container');
    if (messagesContainer) {
        // Smooth scroll to bottom
        messagesContainer.scrollTo({
            top: messagesContainer.scrollHeight,
            behavior: 'smooth'
        });
    }
}

function markMessagesAsRead() {
    if (!currentConversationId) return;
    
    fetch('/chat/messages/mark-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            conversation_id: currentConversationId
        })
    })
    .catch(error => {
        console.error('Error marking messages as read:', error);
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// New Chat Modal Functions
function showNewChatModal() {
    document.getElementById('new-chat-modal').style.display = 'flex';
}

function hideNewChatModal() {
    document.getElementById('new-chat-modal').style.display = 'none';
}

function loadUsersForNewChat() {
    fetch('/api/users')
        .then(response => response.json())
        .then(users => {
            const userList = document.getElementById('user-list');
            if (userList) {
                userList.innerHTML = '';
                
                users.forEach(user => {
                    const userItem = document.createElement('div');
                    userItem.className = 'user-item';
                    userItem.dataset.userId = user.id;
                    userItem.innerHTML = `
                        <div class="user-avatar">${user.name.charAt(0).toUpperCase()}</div>
                        <div class="user-info">
                            <div class="user-name">${user.name}</div>
                            <div class="user-email">${user.email}</div>
                        </div>
                    `;
                    userItem.addEventListener('click', () => startNewChat(user.id));
                    userList.appendChild(userItem);
                });
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
        });
}

function startNewChat(userId) {
    fetch('/chat/start', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            user_id: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideNewChatModal();
            // Reload page to show new conversation
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error starting new chat:', error);
    });
}

// Handle new chat form submission
document.addEventListener('DOMContentLoaded', function() {
    const newChatForm = document.getElementById('new-chat-form');
    if (newChatForm) {
        newChatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const userId = document.getElementById('user_id').value;
            if (userId) {
                startNewChat(userId);
            }
        });
    }
});
