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
        
        <!-- Quản lý hồ sơ -->
        <li class="{{ request()->routeIs('shop.profile.*') ? 'active' : '' }}">
            <a href="{{ route('shop.profile.edit') }}">
                <i class="fas fa-user"></i>
                Hồ sơ
            </a>
        </li>
        
        <!-- Bảo mật -->
        <li class="{{ request()->routeIs('2fa.*') ? 'active' : '' }}">
            <a href="{{ route('2fa.setup') }}">
                <i class="fas fa-shield-alt"></i>
                Xác thực 2 bước
                @if(auth()->user()->google2fa_enabled)
                    <span style="color: #4caf50; margin-left: 8px;" title="Đã bật">
                        <i class="fas fa-check-circle"></i>
                    </span>
                @else
                    <span style="color: #ff9800; margin-left: 8px;" title="Chưa bật">
                        <i class="fas fa-exclamation-triangle"></i>
                    </span>
                @endif
            </a>
        </li>
        
        <!-- Tạm thời comment các menu chưa có controller -->
        {{-- 
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
        --}}
    </ul>
    <script src="{{ asset('js/dashboard/sidebar.js') }}"></script>
</div>
