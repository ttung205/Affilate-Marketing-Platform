<div class="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0">
            <i class="fas fa-share-alt text-primary"></i>
            Publisher
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
            <a href="{{ route('publisher.affiliate-links.index') }}">
                <i class="fas fa-link"></i>
                <span>Affiliate Links</span>
            </a>
        </li>

        <!-- Quản lý Sản phẩm -->
        <li class="{{ request()->routeIs('publisher.products.*') ? 'active' : '' }}">
            <a href="{{ route('publisher.products.index') }}">
                <i class="fas fa-box"></i>
                Sản phẩm
            </a>
        </li>

        <!-- Chiến dịch -->
        <li class="{{ request()->routeIs('publisher.campaigns.*') ? 'active' : '' }}">
            <a href="{{ route('publisher.campaigns.index') }}">
                <i class="fas fa-bullhorn"></i>
                Chiến dịch
            </a>
        </li>

        <!-- Hệ thống Hạng -->
        <li class="sidebar-menu-item {{ request()->routeIs('publisher.ranking.*') ? 'active' : '' }}">
            <a href="#" class="sidebar-menu-link has-submenu" onclick="toggleSubmenu(this)">
                <i class="fas fa-trophy"></i>
                <span>Hệ thống Hạng</span>
                <i class="fas fa-chevron-down submenu-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
                <li class="{{ request()->routeIs('publisher.ranking.index') ? 'active' : '' }}">
                    <a href="{{ route('publisher.ranking.index') }}">
                        <i class="fas fa-medal"></i>
                        Hạng của tôi
                    </a>
                </li>
                <li class="{{ request()->routeIs('publisher.ranking.leaderboard') ? 'active' : '' }}">
                    <a href="{{ route('publisher.ranking.leaderboard') }}">
                        <i class="fas fa-crown"></i>
                        Bảng xếp hạng
                    </a>
                </li>
            </ul>
        </li>

        <!-- Quản lý ví & Thanh toán -->
        <li
            class="sidebar-menu-item {{ request()->routeIs('publisher.wallet.*') || request()->routeIs('publisher.withdrawal.*') || request()->routeIs('publisher.payment-methods.*') ? 'active' : '' }}">
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
                        Tài khoản thanh toán
                    </a>
                </li>
            </ul>
        </li>
    </ul>
    <script src="{{ asset('js/dashboard/sidebar.js') }}"></script>
</div>