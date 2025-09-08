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
                        @elseif(request()->routeIs('publisher.products.create'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Thêm sản phẩm</span>
                            </li>
                        @elseif(request()->routeIs('publisher.products.edit'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Sửa sản phẩm</span>
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
                        @elseif(request()->routeIs('publisher.campaigns.create'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Tạo Campaign</span>
                            </li>
                        @elseif(request()->routeIs('publisher.campaigns.edit'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Sửa Campaign</span>
                            </li>
                        @elseif(request()->routeIs('publisher.campaigns.show'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Chi tiết Campaign</span>
                            </li>
                        @endif
                    @elseif(request()->routeIs('publisher.reports.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Báo cáo</span>
                        </li>
                        @if(request()->routeIs('publisher.reports.performance'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Hiệu suất</span>
                            </li>
                        @elseif(request()->routeIs('publisher.reports.commissions'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Hoa hồng</span>
                            </li>
                        @elseif(request()->routeIs('publisher.reports.clicks'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Lượt click</span>
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
                    @elseif(request()->routeIs('publisher.settings.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Cài đặt</span>
                        </li>
                        @if(request()->routeIs('publisher.settings.index'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Cài đặt chung</span>
                            </li>
                        @elseif(request()->routeIs('publisher.settings.create'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Tạo cài đặt</span>
                            </li>
                        @elseif(request()->routeIs('publisher.settings.edit'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Sửa cài đặt</span>
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
                    <button onclick="markAllAsRead()" class="mark-all-read">Đánh dấu tất cả</button>
                </div>
                <div class="notification-list">
                    <div class="notification-item">
                        <div class="notification-icon">
                            <i class="fas fa-info-circle text-info"></i>
                        </div>
                        <div class="notification-content">
                            <p>Chào mừng bạn đến với Publisher Dashboard!</p>
                            <span class="notification-time">Vừa xong</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Menu -->
        <div class="user-dropdown">
            <button class="user-btn" onclick="toggleUserMenu()">
                <div class="user-avatar">
                    @if(Auth::user()->avatar)
                        <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="Avatar" class="user-avatar-img">
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
                                <img src="{{ Storage::url(Auth::user()->avatar) }}" alt="Avatar" class="user-avatar-img">
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
                    <a href="#" class="user-menu-item">
                        <i class="fas fa-cog"></i>
                        Cài đặt
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