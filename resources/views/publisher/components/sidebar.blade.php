<div class="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0">
            <i class="fas fa-share-alt text-primary"></i>
            PublisherAdmin
        </h4>
    </div>
    
    <ul class="sidebar-menu">
        <li class="{{ request()->routeIs('publisher.dashboard') ? 'active' : '' }}">
            <a href="{{ route('publisher.dashboard') }}">
                <i class="fas fa-tachometer-alt"></i>
                Tổng Quan
            </a>
        </li>
        
        <!-- Quản lý Affiliate Links -->
        <li class="sidebar-menu-item {{ request()->routeIs('publisher.affiliate-links.*') ? 'active' : '' }}">
            <a href="#" class="sidebar-menu-link has-submenu" onclick="toggleSubmenu(this)">
                <i class="fas fa-link"></i>
                <span>Affiliate Links</span>
                <i class="fas fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
                <li class="{{ request()->routeIs('publisher.affiliate-links.index') ? 'active' : '' }}">
                    <a href="{{ route('publisher.affiliate-links.index') }}">
                        <i class="fas fa-list"></i>
                        Tất cả links
                    </a>
                </li>
                <li class="{{ request()->routeIs('publisher.affiliate-links.create') ? 'active' : '' }}">
                    <a href="{{ route('publisher.affiliate-links.create') }}">
                        <i class="fas fa-plus"></i>
                        Tạo link mới
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Quản lý Sản phẩm -->
        <li class="{{ request()->routeIs('publisher.products.*') ? 'active' : '' }}">
            <a href="{{ route('publisher.products.index') }}">
                <i class="fas fa-box"></i>
                Sản phẩm
            </a>
        </li>
        
        <!-- Quản lý ví & Thanh toán -->
        <li class="sidebar-menu-item {{ request()->routeIs('publisher.wallet.*') || request()->routeIs('publisher.withdrawal.*') || request()->routeIs('publisher.payment-methods.*') ? 'active' : '' }}">
            <a href="#" class="sidebar-menu-link has-submenu" onclick="toggleSubmenu(this)">
                <i class="fas fa-wallet"></i>
                <span>Ví & Thanh toán</span>
                <i class="fas fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
                <li class="{{ request()->routeIs('publisher.wallet.*') ? 'active' : '' }}">
                    <a href="{{ route('publisher.wallet.index') }}">
                        <i class="fas fa-wallet"></i>
                        Ví của tôi
                    </a>
                </li>
                <li class="{{ request()->routeIs('publisher.withdrawal.*') ? 'active' : '' }}">
                    <a href="{{ route('publisher.withdrawal.index') }}">
                        <i class="fas fa-money-bill-wave"></i>
                        Rút tiền
                    </a>
                </li>
                <li class="{{ request()->routeIs('publisher.payment-methods.*') ? 'active' : '' }}">
                    <a href="{{ route('publisher.payment-methods.index') }}">
                        <i class="fas fa-credit-card"></i>
                        Phương thức thanh toán
                    </a>
                </li>
            </ul>
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
    </ul>
    <script src="{{ asset('js/dashboard/sidebar.js') }}"></script>
</div>


