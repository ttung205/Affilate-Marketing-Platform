<header class="header">
    <div class="header-left">
        <div class="breadcrumb-container">
            <nav class="breadcrumb-nav" aria-label="breadcrumb">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item">
                        <a href="{{ route('publisher.dashboard') }}" class="breadcrumb-link">
                            <i class="fas fa-home"></i>
                            <span>Publisher</span>
                        </a>
                    </li>

                    @if(request()->routeIs('publisher.dashboard'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item active">
                            <span>Dashboard</span>
                        </li>
                    @elseif(request()->routeIs('publisher.products.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Sản phẩm</span>
                        </li>
                        @if(request()->routeIs('publisher.products.index'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Tất cả sản phẩm</span>
                            </li>

                        @elseif(request()->routeIs('publisher.products.show'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Chi tiết sản phẩm</span>
                            </li>
                        @endif
                    @elseif(request()->routeIs('publisher.affiliate-links.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Affiliate Links</span>
                        </li>
                        @if(request()->routeIs('publisher.affiliate-links.index'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Tất cả Affiliate Links</span>
                            </li>
                        @elseif(request()->routeIs('publisher.affiliate-links.create'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Tạo Affiliate Link</span>
                            </li>
                        @elseif(request()->routeIs('publisher.affiliate-links.edit'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Sửa Affiliate Link</span>
                            </li>
                        @elseif(request()->routeIs('publisher.affiliate-links.show'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Chi tiết Affiliate Link</span>
                            </li>
                        @endif
                    @elseif(request()->routeIs('publisher.campaigns.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Campaigns</span>
                        </li>
                        @if(request()->routeIs('publisher.campaigns.index'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Tất cả Campaigns</span>
                            </li>

                        @elseif(request()->routeIs('publisher.campaigns.show'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Chi tiết Campaign</span>
                            </li>
                        @endif
                    @elseif(request()->routeIs('publisher.payments.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Thanh toán</span>
                        </li>
                        @if(request()->routeIs('publisher.payments.index'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Danh sách thanh toán</span>
                            </li>
                        @elseif(request()->routeIs('publisher.payments.create'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Tạo yêu cầu thanh toán</span>
                            </li>
                        @elseif(request()->routeIs('publisher.payments.show'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Chi tiết thanh toán</span>
                            </li>
                        @endif
                    @elseif(request()->routeIs('publisher.profile.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Hồ sơ</span>
                        </li>
                        @if(request()->routeIs('publisher.profile.edit'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Chỉnh sửa hồ sơ</span>
                            </li>
                        @endif
                    @elseif(request()->routeIs('2fa.setup'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item active">
                            <span>Xác thực 2 bước</span>
                        </li>
                    @elseif(request()->routeIs('publisher.ranking.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Hệ thống hạng</span>
                        </li>
                        @if(request()->routeIs('publisher.ranking.index'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Hạng của tôi</span>
                            </li>
                        @elseif(request()->routeIs('publisher.ranking.leaderboard'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Bảng xếp hạng</span>
                            </li>
                        @endif
                    @elseif(request()->routeIs('publisher.wallet.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Ví & Thanh toán</span>
                        </li>
                        @if(request()->routeIs('publisher.wallet.index'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Ví của tôi</span>
                            </li>
                        @endif
                    @elseif(request()->routeIs('publisher.withdrawal.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Rút tiền</span>
                        </li>
                    @elseif(request()->routeIs('publisher.payment-methods.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Tài khoản thanh toán</span>
                        </li>
                        @if(request()->routeIs('publisher.payment-methods.index'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Danh sách tài khoản</span>
                            </li>
                        @endif
                    @endif
                </ol>
            </nav>
        </div>
    </div>

    <div class="header-right">
        <!-- Notifications -->
        <div class="notification-dropdown">
            <button class="notification-btn" onclick="toggleNotifications()">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">0</span>
            </button>
            <div class="notification-menu" id="notificationMenu">
                <div class="notification-header">
                    <h6>Thông báo</h6>
                    <div class="notification-header-actions">
                        <button onclick="markAllAsRead()" class="mark-all-read">Đánh dấu tất cả</button>
                        <button onclick="realtimeNotifications.showAllNotifications()" class="view-all-btn"
                            title="Xem tất cả">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
                <div class="notification-list" id="notificationList">
                    <!-- Notifications will be loaded here by JavaScript -->
                </div>
                <div class="notification-footer">
                    <button onclick="realtimeNotifications.showAllNotifications()" class="view-all-notifications">
                        <i class="fas fa-list"></i> Xem tất cả thông báo
                    </button>
                </div>
            </div>
        </div>

        <!-- User Menu -->
        <div class="user-dropdown">
            <button class="user-btn" onclick="toggleUserMenu()">
                <div class="user-avatar">
                    @if(Auth::user()->avatar)
                        @if(str_starts_with(Auth::user()->avatar, 'http'))
                            <img src="{{ Auth::user()->avatar }}" alt="Avatar" class="user-avatar-img">
                        @else
                            <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="Avatar" class="user-avatar-img">
                        @endif
                    @else
                        <i class="fas fa-user-circle fa-lg"></i>
                    @endif
                </div>
                <span class="user-name">{{ Auth::user()->name }}</span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="user-menu" id="userMenu">
                <div class="user-menu-header">
                    <div class="user-info">
                        <div class="user-avatar">
                            @if(Auth::user()->avatar)
                                @if(str_starts_with(Auth::user()->avatar, 'http'))
                                    <img src="{{ Auth::user()->avatar }}" alt="Avatar" class="user-avatar-img">
                                @else
                                    <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="Avatar" class="user-avatar-img">
                                @endif
                            @else
                                <i class="fas fa-user-circle fa-2x"></i>
                            @endif
                        </div>
                        <div class="user-details">
                            <div class="user-name">{{ Auth::user()->name }}</div>
                            <div class="user-email">{{ Auth::user()->email }}</div>
                            <div class="user-role">Publisher</div>
                        </div>
                    </div>
                </div>
                <div class="user-menu-items">
                    <a href="{{ route('publisher.profile.edit') }}" class="user-menu-item">
                        <i class="fas fa-user-edit"></i>
                        Chỉnh sửa hồ sơ
                    </a>
                    <a href="{{ route('2fa.setup') }}" class="user-menu-item">
                        <i class="fas fa-shield-alt"></i>
                        Xác thực 2 bước
                        @if(auth()->user()->google2fa_enabled)
                            <span style="color: #4caf50; margin-left: auto;">
                                <i class="fas fa-check-circle"></i>
                            </span>
                        @else
                            <span style="color: #ff9800; margin-left: auto;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                        @endif
                    </a>
                    <div class="user-menu-divider"></div>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <a href="{{ route('logout') }}" class="user-menu-item logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            Đăng xuất
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // Toggle notifications
    function toggleNotifications() {
        const menu = document.getElementById('notificationMenu');
        if (menu) {
            menu.classList.toggle('show');
        }
    }

    // Toggle user menu
    function toggleUserMenu() {
        const menu = document.getElementById('userMenu');
        if (menu) {
            menu.classList.toggle('show');
        }
    }

    // Mark all notifications as read
    function markAllAsRead() {
        if (window.realtimeNotifications) {
            window.realtimeNotifications.markAllAsRead();
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function (event) {
        const notificationMenu = document.getElementById('notificationMenu');
        const userMenu = document.getElementById('userMenu');

        if (notificationMenu && !event.target.closest('.notification-dropdown')) {
            notificationMenu.classList.remove('show');
        }

        if (userMenu && !event.target.closest('.user-dropdown')) {
            userMenu.classList.remove('show');
        }
    });
</script>