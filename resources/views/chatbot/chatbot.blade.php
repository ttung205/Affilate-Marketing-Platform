<!-- Chatbot Widget -->
<div id="chatbot-widget" class="chatbot-widget">
    <!-- Chat Toggle Button -->
    <div id="chatbot-toggle" class="chatbot-toggle">
        <div class="chatbot-toggle-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 2048 2048">
                <path fill="currentColor"
                    d="M768 1024H640V896h128v128zm512 0h-128V896h128v128zm512-128v256h-128v320q0 40-15 75t-41 61t-61 41t-75 15h-264l-440 376v-376H448q-40 0-75-15t-61-41t-41-61t-15-75v-320H128V896h128V704q0-40 15-75t41-61t61-41t75-15h448V303q-29-17-46-47t-18-64q0-27 10-50t27-40t41-28t50-10q27 0 50 10t40 27t28 41t10 50q0 34-17 64t-47 47v209h448q40 0 75 15t61 41t41 61t15 75v192h128zm-256-192q0-26-19-45t-45-19H448q-26 0-45 19t-19 45v768q0 26 19 45t45 19h448v226l264-226h312q26 0 45-19t19-45V704zm-851 462q55 55 126 84t149 30q78 0 149-29t126-85l90 91q-73 73-167 112t-198 39q-103 0-197-39t-168-112l90-91z" />
            </svg>
        </div>
        <div class="chatbot-toggle-badge" id="chatbot-badge">0</div>
    </div>

    <!-- Chat Window -->
    <div id="chatbot-window" class="chatbot-window">
        <!-- Chat Header -->
        <div class="chatbot-header">
            <div class="chatbot-header-info">
                <div class="chatbot-avatar">
                    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="16" cy="16" r="16" fill="#3B82F6" />
                        <path
                            d="M16 8C18.2091 8 20 9.79086 20 12C20 14.2091 18.2091 16 16 16C13.7909 16 12 14.2091 12 12C12 9.79086 13.7909 8 16 8Z"
                            fill="white" />
                        <path d="M16 18C11.5817 18 8 21.5817 8 26H24C24 21.5817 20.4183 18 16 18Z" fill="white" />
                    </svg>
                </div>
                <div class="chatbot-header-text">
                    <h4 class="chatbot-title">Hỗ trợ trực tuyến</h4>
                    <p class="chatbot-subtitle" id="chatbot-role-subtitle">Chúng tôi đang trực tuyến</p>
                </div>
            </div>
            <button id="chatbot-close" class="chatbot-close">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </div>

        <!-- Chat Messages -->
        <div id="chatbot-messages" class="chatbot-messages">
            <div class="chatbot-message chatbot-message-bot">
                <div class="chatbot-message-avatar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="12" fill="#3B82F6" />
                        <path
                            d="M12 6C13.6569 6 15 7.34315 15 9C15 10.6569 13.6569 12 12 12C10.3431 12 9 10.6569 9 9C9 7.34315 10.3431 6 12 6Z"
                            fill="white" />
                        <path d="M12 14C8.68629 14 6 16.6863 6 20H18C18 16.6863 15.3137 14 12 14Z" fill="white" />
                    </svg>
                </div>
                <div class="chatbot-message-content">
                    <div class="chatbot-message-bubble">
                        <p>Xin chào! Tôi là trợ lý ảo của hệ thống affiliate marketing. Tôi có thể giúp gì cho bạn?</p>
                        <div class="chatbot-message-time" id="welcome-time"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div id="chatbot-quick-actions" class="chatbot-quick-actions">
            <!-- Quick actions sẽ được thêm động theo role -->
        </div>

        <!-- Chat Input -->
        <div class="chatbot-input-container">
            <div class="chatbot-input-wrapper">
                <input type="text" id="chatbot-input" class="chatbot-input" placeholder="Nhập tin nhắn của bạn...">
                <button id="chatbot-send" class="chatbot-send-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 8 8">
                        <path fill="currentColor" d="M3.47 0L1 3h2v5h1V3h2L3.47 0z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="chatbot-loading" class="chatbot-loading">
    <div class="chatbot-loading-spinner"></div>
    <p>Đang xử lý...</p>
</div>

<script>
    // Pass user role to JavaScript
    window.userRole = '{{ Auth::user()->role ?? "guest" }}';
    window.userName = '{{ Auth::user()->name ?? "Khách" }}';

    // Add CSRF token to head if not exists
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = '{{ csrf_token() }}';
        document.head.appendChild(meta);
    }
</script>