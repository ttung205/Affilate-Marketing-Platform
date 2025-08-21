<div class="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0">
            <i class="fas fa-store text-primary"></i>
            ShopAdmin
        </h4>
    </div>
    
    <ul class="sidebar-menu">
        <li class="{{ request()->routeIs('shop.dashboard') ? 'active' : '' }}">
            <a href="{{ route('shop.dashboard') }}">
                <i class="fas fa-tachometer-alt"></i>
                Tổng Quan
            </a>
        </li>
        <!-- Quản lý sản phẩm với submenu -->
        <li class="sidebar-menu-item {{ request()->routeIs('shop.products.*') ? 'active' : '' }}">
            <a href="#" class="sidebar-menu-link has-submenu" onclick="toggleSubmenu(this)">
                <i class="fas fa-box"></i>
                <span>Quản lý Sản phẩm</span>
                <i class="fas fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
                <li class="{{ request()->routeIs('shop.products.index') ? 'active' : '' }}">
                    <a href="{{ route('shop.products.index') }}">
                        <i class="fas fa-list"></i>
                        Tất cả sản phẩm
                    </a>
                </li>
                <li class="{{ request()->routeIs('shop.products.create') ? 'active' : '' }}">
                    <a href="{{ route('shop.products.create') }}">
                        <i class="fas fa-plus"></i>
                        Thêm sản phẩm
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Quản lý đơn hàng -->
        <li class="{{ request()->routeIs('shop.orders.*') ? 'active' : '' }}">
            <a href="{{ route('shop.orders.index') }}">
                <i class="fas fa-shopping-cart"></i>
                Quản lý đơn hàng
            </a>
        </li>
        
        <!-- Quản lý doanh thu -->
        <li class="{{ request()->routeIs('shop.revenue.*') ? 'active' : '' }}">
            <a href="{{ route('shop.revenue.index') }}">
                <i class="fas fa-chart-line"></i>
                Doanh thu
            </a>
        </li>
        
        <!-- Quản lý thanh toán -->
        <li class="{{ request()->routeIs('shop.payments.*') ? 'active' : '' }}">
            <a href="{{ route('shop.payments.index') }}">
                <i class="fas fa-credit-card"></i>
                Thanh toán
            </a>
        </li>
        
        <!-- Cài đặt -->
        <li class="{{ request()->routeIs('shop.settings.*') ? 'active' : '' }}">
            <a href="{{ route('shop.settings.index') }}">
                <i class="fas fa-cog"></i>
                Cài đặt
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">
                <i class="fas fa-user-circle fa-2x"></i>
            </div>
            <div class="user-details">
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="user-role">Shop Owner</div>
            </div>
        </div>
        <a href="{{ route('logout') }}" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            Đăng xuất
        </a>
    </div>
    <script src="{{ asset('js/dashboard/sidebar.js') }}"></script>
</div>
