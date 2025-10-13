<div class="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0">
            <i class="fas fa-chart-line text-primary"></i>
            AffiliateAdmin
        </h4>
    </div>
    
    <ul class="sidebar-menu">
        <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}">
                <i class="fas fa-tachometer-alt"></i>
                Tổng Quan
            </a>
        </li>
        <!-- Quản lý sản phẩm với submenu -->
        <li class="sidebar-menu-item {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <a href="{{ route('admin.products.index') }}">
                <i class="fas fa-box"></i>
                Quản lý Sản phẩm
            </a>
        </li>
        <!-- Quản lý danh mục với submenu -->
        <li class="{{ request()->routeIs('admin.categories.index') ? 'active' : '' }}">
            <a href="{{ route('admin.categories.index') }}">
                <i class="fas fa-tags"></i>
                Quản lý Danh mục
            </a>
        </li>
        <!-- Quản lý người dùng với submenu -->
        <li class="sidebar-menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <a href="{{ route('admin.users.index') }}">
                <i class="fas fa-users"></i>
                <span>Quản lý Người dùng</span>
            </a>
        </li>
        
        <!-- Quản lý Affiliate với submenu -->
        <li class="sidebar-menu-item {{ request()->routeIs('admin.affiliate-links.*') || request()->routeIs('admin.campaigns.*') ? 'active' : '' }}">
            <a href="{{ route('admin.affiliate-links.index') }}">
                <i class="fas fa-link"></i>
                <span>Quản lý Affiliate Links</span>
            </a>
        </li>
        <!-- Quản lý Chiến dịch -->
        <li class="sidebar-menu-item {{ request()->routeIs('admin.campaigns.*') ? 'active' : '' }}">
            <a href="{{ route('admin.campaigns.index') }}">
                <i class="fas fa-bullhorn"></i>
                <span>Quản lý Chiến dịch</span>
            </a>
        </li>
        <!-- Quản lý Rút tiền -->
        <li class="{{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
            <a href="{{ route('admin.withdrawals.index') }}">
                <i class="fas fa-money-bill-wave"></i>
                <span>Quản lý Rút tiền</span>
            </a>
        </li>
        
        <!-- Quản lý Phí sàn với submenu -->
        <li class="sidebar-menu-item {{ request()->routeIs('admin.platform-fees.*') || request()->routeIs('admin.platform-fee-payments.*') ? 'active' : '' }}">
            <a href="#" class="sidebar-menu-link has-submenu" onclick="toggleSubmenu(this)">
                <i class="fas fa-percentage"></i>
                <span>Quản lý Phí sàn</span>
                <i class="fas fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
                <li class="{{ request()->routeIs('admin.platform-fees.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.platform-fees.index') }}">
                        <i class="fas fa-cog"></i>
                        Cài đặt phí sàn
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.platform-fee-payments.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.platform-fee-payments.index', ['status' => 'pending']) }}">
                        <i class="fas fa-check-circle"></i>
                        Duyệt thanh toán
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Quản lý Thông báo -->
        <li class="{{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
            <a href="{{ route('admin.notifications.manage') }}">
                <i class="fas fa-bell"></i>
                <span>Quản lý Thông báo</span>
            </a>
        </li>

        <!-- Fraud Detection -->
        <li class="{{ request()->routeIs('admin.fraud-detection.*') ? 'active' : '' }}">
            <a href="{{ route('admin.fraud-detection.index') }}">
                <i class="fas fa-shield-alt"></i>
                <span>Fraud Detection</span>
                <span class="badge bg-danger ms-2">New</span>
            </a>
        </li>
    </ul>
    <script src="{{ asset('js/dashboard/sidebar.js') }}"></script>
</div>