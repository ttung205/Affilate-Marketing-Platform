@if(auth()->user()->role === 'shop')
    @extends('shop.layouts.app')
@elseif(auth()->user()->role === 'publisher')
    @extends('publisher.layouts.app')
@elseif(auth()->user()->role === 'admin')
    @extends('components.dashboard.layout')
@else
    @extends('shop.layouts.app')
@endif

@section('title', 'Tin nhắn')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/chat/chat.css') }}">
@endpush

@section('content')
<div class="chat-layout">
    <!-- Left Sidebar: Conversations List -->
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <h3><i class="fas fa-comments"></i> Tin nhắn</h3>
            <button class="btn btn-primary btn-sm" onclick="showNewChatModal()">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        
        <div class="conversations-list" id="conversations-list">
            @forelse($conversations as $conversation)
                @php
                    $otherUser = $conversation->getOtherParticipant(auth()->id());
                    $unreadCount = $conversation->unreadMessagesCount(auth()->id());
                @endphp
                <div class="conversation-item {{ $loop->first ? 'active' : '' }}" 
                     data-conversation-id="{{ $conversation->id }}"
                     onclick="loadConversation({{ $conversation->id }})">
                    <div class="conversation-avatar">
                        <div class="avatar-circle {{ $otherUser->role }}">
                            {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="conversation-details">
                        <div class="conversation-header">
                            <h4 class="conversation-name">{{ $otherUser->name }}</h4>
                            <span class="conversation-time">
                                {{ $conversation->last_message_at ? $conversation->last_message_at->format('H:i') : '' }}
                            </span>
                        </div>
                        <div class="conversation-preview">
                            <p class="last-message">
                                {{ $conversation->last_message_preview ?? 'Chưa có tin nhắn' }}
                            </p>
                            @if($unreadCount > 0)
                                <span class="unread-badge">{{ $unreadCount }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="no-conversations">
                    <i class="fas fa-comment-slash"></i>
                    <p>Chưa có cuộc trò chuyện nào</p>
                    <button class="btn btn-primary btn-sm" onclick="showNewChatModal()">
                        Bắt đầu trò chuyện
                    </button>
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Right Panel: Chat Window -->
    <div class="chat-main">
        @if($conversations->count() > 0)
            @php
                $firstConversation = $conversations->first();
                $otherUser = $firstConversation->getOtherParticipant(auth()->id());
            @endphp
            <div id="chat-window">
                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="chat-participant-info">
                        <div class="participant-avatar">
                            <div class="avatar-circle {{ $otherUser->role }}">
                                {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                            </div>
                        </div>
                        <div class="participant-details">
                            <h4 id="participant-name">{{ $otherUser->name }}</h4>
                            <p class="participant-role">
                                <span class="role-badge {{ $otherUser->role }}">
                                    {{ $otherUser->role === 'shop' ? 'Shop' : 'Publisher' }}
                                </span>
                                <span id="participant-email">{{ $otherUser->email }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Messages Area -->
                <div class="messages-container" id="messages-container">
                    <div class="messages-list" id="messages-list">
                        <div class="loading-messages">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Đang tải tin nhắn...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Message Input -->
                <div class="message-input-area">
                    <form id="message-form" class="message-form">
                        <div class="input-group">
                            <textarea 
                                id="message-input" 
                                name="message" 
                                placeholder="Nhập tin nhắn..." 
                                rows="1"
                                required
                            ></textarea>
                            <button type="submit" class="send-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    
                    <div class="typing-indicator" id="typing-indicator" style="display: none;">
                        <span class="typing-text">Đang nhập...</span>
                    </div>
                </div>
            </div>
        @else
            <div class="no-chat-selected">
                <i class="fas fa-comments"></i>
                <h3>Chưa có cuộc trò chuyện</h3>
                <p>Chọn hoặc tạo cuộc trò chuyện để bắt đầu</p>
                <button class="btn btn-primary" onclick="showNewChatModal()">
                    <i class="fas fa-plus"></i> Tạo cuộc trò chuyện mới
                </button>
            </div>
        @endif
    </div>
</div>

<!-- New Chat Modal -->
<div id="new-chat-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Bắt đầu cuộc trò chuyện mới</h3>
            <span class="close" onclick="hideNewChatModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="new-chat-form">
                <div class="form-group">
                    <label for="user-select">Chọn người nhận:</label>
                    <select id="user-select" name="user_id" required>
                        <option value="">-- Chọn người dùng --</option>
                        <!-- Options will be loaded via AJAX -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="first-message">Tin nhắn đầu tiên:</label>
                    <textarea id="first-message" name="message" rows="3" placeholder="Nhập tin nhắn..." required></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideNewChatModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Gửi tin nhắn</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Set global userId for chat.js
    window.userId = {{ auth()->id() }};
</script>
<script src="{{ asset('js/chat/chat.js') }}"></script>
@endpush