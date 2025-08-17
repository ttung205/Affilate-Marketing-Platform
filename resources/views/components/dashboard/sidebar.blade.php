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
        <!-- Tạm thời comment các menu chưa có route -->
        {{-- <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <a href="{{ route('admin.users.index') }}">
                <i class="fas fa-users"></i>
                Quản lý người dùng
            </a>
        </li>
        
        <li class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <a href="{{ route('admin.orders.index') }}">
                <i class="fas fa-shopping-cart"></i>
                Quản lý đơn hàng
            </a>
        </li> --}}
    </ul>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">
                <i class="fas fa-user-circle fa-2x"></i>
            </div>
            <div class="user-details">
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="user-role">{{ Auth::user()->role }}</div>
            </div>
        </div>
        <a href="{{ route('logout') }}" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            Đăng xuất
        </a>
    </div>
    <script src="{{ asset('js/dashboard/sidebar.js') }}"></script>
</div>