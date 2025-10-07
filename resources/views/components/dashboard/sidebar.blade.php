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
            <a href="#" class="sidebar-menu-link has-submenu" onclick="toggleSubmenu(this)">
                <i class="fas fa-box"></i>
                <span>Quản lý Sản phẩm</span>
                <i class="fas fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
                <li class="{{ request()->routeIs('admin.products.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.products.index') }}">
                        <i class="fas fa-list"></i>
                        Tất cả sản phẩm
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.products.create') ? 'active' : '' }}">
                    <a href="{{ route('admin.products.create') }}">
                        <i class="fas fa-plus"></i>
                        Thêm sản phẩm
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.categories.index') }}">
                        <i class="fas fa-tags"></i>
                        Danh mục
                    </a>
                </li>
            </ul>
        </li>
        <!-- Quản lý người dùng với submenu -->
        <li class="sidebar-menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <a href="#" class="sidebar-menu-link has-submenu" onclick="toggleSubmenu(this)">
                <i class="fas fa-users"></i>
                <span>Quản lý Người dùng</span>
                <i class="fas fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
                <li class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}">
                        <i class="fas fa-list"></i>
                        Tất cả người dùng
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.create') }}">
                        <i class="fas fa-plus"></i>
                        Thêm người dùng
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Quản lý Affiliate với submenu -->
        <li class="sidebar-menu-item {{ request()->routeIs('admin.affiliate-links.*') || request()->routeIs('admin.campaigns.*') ? 'active' : '' }}">
            <a href="#" class="sidebar-menu-link has-submenu" onclick="toggleSubmenu(this)">
                <i class="fas fa-link"></i>
                <span>Quản lý Affiliate</span>
                <i class="fas fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
                <li class="{{ request()->routeIs('admin.affiliate-links.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.affiliate-links.index') }}">
                        <i class="fas fa-link"></i>
                        Affiliate Links
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.campaigns.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.campaigns.index') }}">
                        <i class="fas fa-bullhorn"></i>
                        Campaigns
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Quản lý Rút tiền -->
        <li class="{{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
            <a href="{{ route('admin.withdrawals.index') }}">
                <i class="fas fa-money-bill-wave"></i>
                <span>Quản lý Rút tiền</span>
            </a>
        </li>
        
        <!-- Quản lý Phí sàn -->
        <li class="{{ request()->routeIs('admin.platform-fee.*') ? 'active' : '' }}">
            <a href="{{ route('admin.platform-fee.index') }}">
                <i class="fas fa-percentage"></i>
                <span>Quản lý Phí sàn</span>
            </a>
        </li>
        
        <!-- Quản lý Thông báo -->
        <li class="{{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
            <a href="{{ route('admin.notifications.index') }}">
                <i class="fas fa-bell"></i>
                <span>Quản lý Thông báo</span>
            </a>
        </li>
    </ul>
    <script src="{{ asset('js/dashboard/sidebar.js') }}"></script>
</div>