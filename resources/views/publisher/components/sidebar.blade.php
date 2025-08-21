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
        
        <!-- Quản lý Campaigns -->
        <li class="{{ request()->routeIs('publisher.campaigns.*') ? 'active' : '' }}">
            <a href="{{ route('publisher.campaigns.index') }}">
                <i class="fas fa-bullhorn"></i>
                Chiến dịch
            </a>
        </li>
        
        <!-- Quản lý Sản phẩm -->
        <li class="{{ request()->routeIs('publisher.products.*') ? 'active' : '' }}">
            <a href="{{ route('publisher.products.index') }}">
                <i class="fas fa-box"></i>
                Sản phẩm
            </a>
        </li>
        
        <!-- Báo cáo & Analytics -->
        <li class="sidebar-menu-item {{ request()->routeIs('publisher.reports.*') ? 'active' : '' }}">
            <a href="#" class="sidebar-menu-link has-submenu" onclick="toggleSubmenu(this)">
                <i class="fas fa-chart-bar"></i>
                <span>Báo cáo</span>
                <i class="fas fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
                <li class="{{ request()->routeIs('publisher.reports.performance') ? 'active' : '' }}">
                    <a href="{{ route('publisher.reports.performance') }}">
                        <i class="fas fa-chart-line"></i>
                        Hiệu suất
                    </a>
                </li>
                <li class="{{ request()->routeIs('publisher.reports.commissions') ? 'active' : '' }}">
                    <a href="{{ route('publisher.reports.commissions') }}">
                        <i class="fas fa-dollar-sign"></i>
                        Hoa hồng
                    </a>
                </li>
                <li class="{{ request()->routeIs('publisher.reports.clicks') ? 'active' : '' }}">
                    <a href="{{ route('publisher.reports.clicks') }}">
                        <i class="fas fa-mouse-pointer"></i>
                        Lượt click
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Quản lý thanh toán -->
        <li class="{{ request()->routeIs('publisher.payments.*') ? 'active' : '' }}">
            <a href="{{ route('publisher.payments.index') }}">
                <i class="fas fa-credit-card"></i>
                Thanh toán
            </a>
        </li>
        
        <!-- Cài đặt -->
        <li class="{{ request()->routeIs('publisher.settings.*') ? 'active' : '' }}">
            <a href="{{ route('publisher.settings.index') }}">
                <i class="fas fa-cog"></i>
                Cài đặt
            </a>
        </li>
    </ul>
    
    <script src="{{ asset('js/dashboard/sidebar.js') }}"></script>
</div>


