/**
 * Affiliate Marketing Chatbot
 * Hỗ trợ 3 role: admin, publisher, shop
 */

class AffiliateChatbot {
    constructor() {
        this.userRole = window.userRole || 'guest';
        this.userName = window.userName || 'Khách';
        this.isOpen = false;
        this.messageCount = 0;
        this.conversationHistory = [];
        
        this.initializeElements();
        this.setupEventListeners();
        this.initializeChatbot();
        this.setCurrentTime();
    }

    initializeElements() {
        this.chatbotWidget = document.getElementById('chatbot-widget');
        this.chatbotToggle = document.getElementById('chatbot-toggle');
        this.chatbotWindow = document.getElementById('chatbot-window');
        this.chatbotClose = document.getElementById('chatbot-close');
        this.chatbotMessages = document.getElementById('chatbot-messages');
        this.chatbotInput = document.getElementById('chatbot-input');
        this.chatbotSend = document.getElementById('chatbot-send');
        this.chatbotBadge = document.getElementById('chatbot-badge');
        this.chatbotLoading = document.getElementById('chatbot-loading');
        this.chatbotQuickActions = document.getElementById('chatbot-quick-actions');
        this.chatbotRoleSubtitle = document.getElementById('chatbot-role-subtitle');
    }

    setupEventListeners() {
        this.chatbotToggle.addEventListener('click', () => this.toggleChat());
        this.chatbotClose.addEventListener('click', () => this.closeChat());
        this.chatbotSend.addEventListener('click', () => this.sendMessage());
        this.chatbotInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // Close chat when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.chatbotWidget.contains(e.target) && this.isOpen) {
                this.closeChat();
            }
        });
    }

    initializeChatbot() {
        // Apply role-specific styling
        this.chatbotWidget.classList.add(`chatbot-role-${this.userRole}`);
        
        // Set role-specific subtitle
        const roleNames = {
            'admin': 'Quản trị viên',
            'publisher': 'Nhà xuất bản',
            'shop': 'Cửa hàng',
            'guest': 'Khách'
        };
        
        this.chatbotRoleSubtitle.textContent = `Xin chào ${this.userName} - ${roleNames[this.userRole] || 'Khách'}`;
        
        // Initialize quick actions based on role
        this.initializeQuickActions();
        
        // Show welcome message based on role
        setTimeout(() => {
            this.showWelcomeMessage();
        }, 1000);
    }

    initializeQuickActions() {
        const quickActions = this.getQuickActionsForRole(this.userRole);
        
        if (quickActions.length > 0) {
            const quickActionsHTML = `
                <div class="chatbot-quick-actions-title">Các câu hỏi thường gặp:</div>
                <div class="chatbot-quick-actions-list">
                    ${quickActions.map(action => 
                        `<button class="chatbot-quick-action" data-action="${action.action}">${action.label}</button>`
                    ).join('')}
                </div>
            `;
            
            this.chatbotQuickActions.innerHTML = quickActionsHTML;
            
            // Add event listeners to quick actions
            this.chatbotQuickActions.querySelectorAll('.chatbot-quick-action').forEach(button => {
                button.addEventListener('click', (e) => {
                    const action = e.target.getAttribute('data-action');
                    this.handleQuickAction(action);
                });
            });
        }
    }

    getQuickActionsForRole(role) {
        const quickActions = {
            'admin': [
                { action: 'admin_dashboard', label: '📊 Tổng quan hệ thống' },
                { action: 'admin_users', label: '👥 Quản lý người dùng' },
                { action: 'admin_reports', label: '📈 Báo cáo thống kê' },
                { action: 'admin_settings', label: '⚙️ Cài đặt hệ thống' }
            ],
            'publisher': [
                { action: 'publisher_links', label: '🔗 Quản lý link affiliate' },
                { action: 'publisher_earnings', label: '💰 Thu nhập của tôi' },
                { action: 'publisher_campaigns', label: '🎯 Chiến dịch hiện tại' },
                { action: 'publisher_help', label: '❓ Hướng dẫn sử dụng' }
            ],
            'shop': [
                { action: 'shop_products', label: '🛍️ Quản lý sản phẩm' },
                { action: 'shop_campaigns', label: '📢 Tạo chiến dịch' },
                { action: 'shop_analytics', label: '📊 Phân tích bán hàng' },
                { action: 'shop_support', label: '🆘 Hỗ trợ kỹ thuật' }
            ],
            'guest': [
                { action: 'guest_info', label: 'ℹ️ Thông tin hệ thống' },
                { action: 'guest_register', label: '📝 Đăng ký tài khoản' },
                { action: 'guest_login', label: '🔑 Đăng nhập' }
            ]
        };
        
        return quickActions[role] || [];
    }

    showWelcomeMessage() {
        const welcomeMessages = {
            'admin': `Chào mừng ${this.userName}! Tôi có thể giúp bạn quản lý hệ thống affiliate marketing. Bạn cần hỗ trợ gì?`,
            'publisher': `Xin chào ${this.userName}! Tôi sẽ hỗ trợ bạn tối ưu hóa thu nhập từ affiliate marketing. Hãy cho tôi biết bạn cần giúp gì!`,
            'shop': `Chào ${this.userName}! Tôi sẽ giúp bạn quản lý cửa hàng và tạo chiến dịch marketing hiệu quả. Bạn muốn làm gì?`,
            'guest': `Xin chào! Tôi là trợ lý ảo của hệ thống affiliate marketing. Bạn có thể đăng ký tài khoản để sử dụng đầy đủ các tính năng.`
        };
        
        this.addBotMessage(welcomeMessages[this.userRole] || welcomeMessages['guest']);
    }

    toggleChat() {
        if (this.isOpen) {
            this.closeChat();
        } else {
            this.openChat();
        }
    }

    openChat() {
        this.chatbotWindow.classList.add('show');
        this.isOpen = true;
        this.chatbotInput.focus();
        this.scrollToBottom();
        this.hideBadge();
    }

    closeChat() {
        this.chatbotWindow.classList.remove('show');
        this.isOpen = false;
    }

    sendMessage() {
        const message = this.chatbotInput.value.trim();
        if (!message) return;

        this.addUserMessage(message);
        this.chatbotInput.value = '';
        
        // Show typing indicator
        this.showTypingIndicator();
        
        // Process message after delay
        setTimeout(() => {
            this.processMessage(message);
        }, 1000 + Math.random() * 1000); // Random delay for realism
    }

    addUserMessage(message) {
        const messageElement = this.createMessageElement(message, 'user');
        this.chatbotMessages.appendChild(messageElement);
        this.scrollToBottom();
        this.conversationHistory.push({ type: 'user', message });
    }

    addBotMessage(message) {
        this.hideTypingIndicator();
        const messageElement = this.createMessageElement(message, 'bot');
        this.chatbotMessages.appendChild(messageElement);
        this.scrollToBottom();
        this.conversationHistory.push({ type: 'bot', message });
    }

    createMessageElement(message, type) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message chatbot-message-${type}`;
        
        const avatar = type === 'user' ? 
            `<div class="chatbot-message-avatar">${this.userName.charAt(0).toUpperCase()}</div>` :
            `<div class="chatbot-message-avatar">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="12" fill="#3B82F6"/>
                    <path d="M12 6C13.6569 6 15 7.34315 15 9C15 10.6569 13.6569 12 12 12C10.3431 12 9 10.6569 9 9C9 7.34315 10.3431 6 12 6Z" fill="white"/>
                    <path d="M12 14C8.68629 14 6 16.6863 6 20H18C18 16.6863 15.3137 14 12 14Z" fill="white"/>
                </svg>
            </div>`;
        
        const time = this.getCurrentTime();
        
        messageDiv.innerHTML = `
            ${avatar}
            <div class="chatbot-message-content">
                <div class="chatbot-message-bubble">
                    <p>${this.escapeHtml(message)}</p>
                    <div class="chatbot-message-time">${time}</div>
                </div>
            </div>
        `;
        
        return messageDiv;
    }

    showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chatbot-message chatbot-message-bot typing-indicator';
        typingDiv.innerHTML = `
            <div class="chatbot-message-avatar">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="12" fill="#3B82F6"/>
                    <path d="M12 6C13.6569 6 15 7.34315 15 9C15 10.6569 13.6569 12 12 12C10.3431 12 9 10.6569 9 9C9 7.34315 10.3431 6 12 6Z" fill="white"/>
                    <path d="M12 14C8.68629 14 6 16.6863 6 20H18C18 16.6863 15.3137 14 12 14Z" fill="white"/>
                </svg>
            </div>
            <div class="chatbot-message-content">
                <div class="chatbot-message-bubble">
                    <div class="chatbot-typing">
                        <div class="chatbot-typing-dot"></div>
                        <div class="chatbot-typing-dot"></div>
                        <div class="chatbot-typing-dot"></div>
                    </div>
                </div>
            </div>
        `;
        
        this.chatbotMessages.appendChild(typingDiv);
        this.scrollToBottom();
    }

    hideTypingIndicator() {
        const typingIndicator = this.chatbotMessages.querySelector('.typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    processMessage(message) {
        const response = this.getResponseForMessage(message.toLowerCase());
        this.addBotMessage(response);
        
        // Update message count and badge
        this.messageCount++;
        this.showBadge();
    }

    getResponseForMessage(message) {
        // Role-specific responses
        const responses = this.getRoleSpecificResponses();
        
        // Check for specific keywords
        for (const [keyword, response] of Object.entries(responses.keywords)) {
            if (message.includes(keyword)) {
                return response;
            }
        }
        
        // Default responses based on role
        return responses.default[Math.floor(Math.random() * responses.default.length)];
    }

    getRoleSpecificResponses() {
        const responses = {
            'admin': {
                keywords: {
                    'dashboard': 'Bạn có thể truy cập dashboard admin để xem tổng quan hệ thống, số liệu thống kê và quản lý người dùng.',
                    'user': 'Trong phần quản lý người dùng, bạn có thể thêm, sửa, xóa và phân quyền cho các tài khoản.',
                    'report': 'Hệ thống cung cấp nhiều loại báo cáo chi tiết về doanh thu, chuyển đổi và hiệu suất.',
                    'setting': 'Cài đặt hệ thống cho phép bạn tùy chỉnh các tham số hoạt động của nền tảng.',
                    'earnings': 'Bạn có thể xem báo cáo tổng doanh thu và hoa hồng của toàn bộ hệ thống.',
                    'campaign': 'Quản lý chiến dịch giúp bạn theo dõi hiệu suất của các chương trình affiliate.'
                },
                default: [
                    'Với vai trò admin, bạn có quyền truy cập vào tất cả các tính năng quản lý hệ thống.',
                    'Bạn có thể quản lý người dùng, xem báo cáo chi tiết và cấu hình hệ thống.',
                    'Hãy sử dụng menu admin để truy cập các chức năng quản lý.'
                ]
            },
            'publisher': {
                keywords: {
                    'link': 'Bạn có thể tạo và quản lý các affiliate link trong phần "Affiliate Links". Mỗi link sẽ có mã tracking riêng.',
                    'earning': 'Thu nhập của bạn được tính dựa trên hoa hồng từ các chuyển đổi thành công.',
                    'campaign': 'Bạn có thể tham gia các chiến dịch và nhận link affiliate để chia sẻ.',
                    'commission': 'Hoa hồng được tính theo tỷ lệ phần trăm hoặc số tiền cố định tùy theo sản phẩm.',
                    'conversion': 'Chuyển đổi là khi ai đó mua hàng thông qua link affiliate của bạn.',
                    'wallet': 'Ví của bạn hiển thị số dư hiện tại và lịch sử giao dịch.'
                },
                default: [
                    'Là publisher, bạn có thể tạo affiliate links và kiếm hoa hồng từ việc giới thiệu sản phẩm.',
                    'Hãy tạo link affiliate và chia sẻ chúng để bắt đầu kiếm thu nhập.',
                    'Theo dõi hiệu suất của các link để tối ưu hóa thu nhập.'
                ]
            },
            'shop': {
                keywords: {
                    'product': 'Bạn có thể thêm, sửa và quản lý sản phẩm trong phần "Sản phẩm".',
                    'campaign': 'Tạo chiến dịch affiliate để publisher có thể quảng bá sản phẩm của bạn.',
                    'analytics': 'Xem báo cáo chi tiết về doanh số, chuyển đổi và hiệu suất marketing.',
                    'order': 'Quản lý đơn hàng và theo dõi trạng thái giao hàng.',
                    'commission': 'Thiết lập tỷ lệ hoa hồng cho từng sản phẩm hoặc chiến dịch.',
                    'publisher': 'Xem danh sách các publisher đang quảng bá sản phẩm của bạn.'
                },
                default: [
                    'Là shop owner, bạn có thể tạo chiến dịch affiliate và quản lý sản phẩm.',
                    'Hãy tạo chiến dịch để thu hút publisher quảng bá sản phẩm của bạn.',
                    'Theo dõi báo cáo để đánh giá hiệu quả marketing.'
                ]
            },
            'guest': {
                keywords: {
                    'register': 'Bạn có thể đăng ký tài khoản để trở thành publisher hoặc shop owner.',
                    'login': 'Đăng nhập để truy cập các tính năng dành riêng cho tài khoản của bạn.',
                    'info': 'Hệ thống affiliate marketing giúp kết nối shop và publisher để tăng doanh số.',
                    'help': 'Bạn có thể liên hệ hỗ trợ hoặc đọc tài liệu hướng dẫn.'
                },
                default: [
                    'Xin chào! Đây là hệ thống affiliate marketing. Bạn có thể đăng ký tài khoản để sử dụng.',
                    'Đăng ký làm publisher để kiếm hoa hồng, hoặc làm shop để tăng doanh số.',
                    'Hãy liên hệ chúng tôi nếu bạn cần hỗ trợ thêm.'
                ]
            }
        };
        
        return responses[this.userRole] || responses['guest'];
    }

    handleQuickAction(action) {
        const responses = {
            'admin_dashboard': 'Dashboard admin cung cấp tổng quan về toàn bộ hệ thống, bao gồm số liệu thống kê, biểu đồ và các chỉ số quan trọng.',
            'admin_users': 'Quản lý người dùng cho phép bạn thêm, chỉnh sửa, xóa tài khoản và phân quyền cho từng role.',
            'admin_reports': 'Báo cáo thống kê bao gồm doanh thu, chuyển đổi, hiệu suất publisher và các chỉ số khác.',
            'admin_settings': 'Cài đặt hệ thống cho phép tùy chỉnh thông số hoạt động, email template, và các cấu hình khác.',
            
            'publisher_links': 'Trong phần quản lý link, bạn có thể tạo link mới, xem thống kê và quản lý các affiliate link hiện có.',
            'publisher_earnings': 'Phần thu nhập hiển thị số dư hiện tại, lịch sử thanh toán và dự báo thu nhập.',
            'publisher_campaigns': 'Chiến dịch hiện tại cho phép bạn tham gia các chương trình affiliate và nhận link quảng bá.',
            'publisher_help': 'Hướng dẫn sử dụng bao gồm cách tạo link, tối ưu hóa thu nhập và các mẹo marketing.',
            
            'shop_products': 'Quản lý sản phẩm cho phép thêm, sửa, xóa sản phẩm và thiết lập thông tin chi tiết.',
            'shop_campaigns': 'Tạo chiến dịch affiliate để thu hút publisher quảng bá sản phẩm của bạn.',
            'shop_analytics': 'Phân tích bán hàng cung cấp insights về doanh số, khách hàng và hiệu suất marketing.',
            'shop_support': 'Hỗ trợ kỹ thuật giúp giải quyết các vấn đề về tích hợp, thanh toán và vận hành.',
            
            'guest_info': 'Hệ thống affiliate marketing giúp kết nối shop và publisher. Shop tạo chiến dịch, publisher quảng bá và nhận hoa hồng.',
            'guest_register': 'Đăng ký tài khoản miễn phí để trở thành publisher (kiếm hoa hồng) hoặc shop (tăng doanh số).',
            'guest_login': 'Đăng nhập vào tài khoản hiện có để truy cập các tính năng dành riêng cho role của bạn.'
        };
        
        const response = responses[action] || 'Tôi không hiểu yêu cầu này. Bạn có thể diễn đạt cụ thể hơn không?';
        this.addBotMessage(response);
    }

    showBadge() {
        if (this.messageCount > 0) {
            this.chatbotBadge.textContent = this.messageCount;
            this.chatbotBadge.classList.add('show');
        }
    }

    hideBadge() {
        this.chatbotBadge.classList.remove('show');
        this.messageCount = 0;
    }

    scrollToBottom() {
        setTimeout(() => {
            this.chatbotMessages.scrollTop = this.chatbotMessages.scrollHeight;
        }, 100);
    }

    getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString('vi-VN', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }

    setCurrentTime() {
        const welcomeTime = document.getElementById('welcome-time');
        if (welcomeTime) {
            welcomeTime.textContent = this.getCurrentTime();
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize chatbot when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new AffiliateChatbot();
});

// Auto-open chatbot for new users (optional)
if (window.userRole === 'guest' && !localStorage.getItem('chatbot_shown')) {
    setTimeout(() => {
        const chatbot = new AffiliateChatbot();
        chatbot.openChat();
        localStorage.setItem('chatbot_shown', 'true');
    }, 3000);
}
