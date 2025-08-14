<div class="header">
    <div class="header-left">
        <div class="breadcrumb-container">
            <nav class="breadcrumb-nav" aria-label="breadcrumb">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">
                            <i class="fas fa-home"></i>
                            <span>Admin</span>
                        </a>
                    </li>
                    @if(request()->routeIs('admin.products.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-separator"></i>
                        </li>
                        <li class="breadcrumb-item active">
                            <span>Quản lý sản phẩm</span>
                        </li>
                    @elseif(request()->routeIs('admin.users.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-separator"></i>
                        </li>
                        <li class="breadcrumb-item active">
                            <span>Quản lý người dùng</span>
                        </li>
                    @elseif(request()->routeIs('admin.orders.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-separator"></i>
                        </li>
                        <li class="breadcrumb-item active">
                            <span>Quản lý đơn hàng</span>
                        </li>
                    @else
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-separator"></i>
                        </li>
                        <li class="breadcrumb-item active">
                            <span>Tổng quan</span>
                        </li>
                    @endif
                </ol>
            </nav>
        </div>
        
    </div>

    <div class="header-right">
        <div class="search-box">
            <input type="text" placeholder="Tìm kiếm..." class="form-control">
        </div>
        
        <div class="notifications">
            <a href="#" class="notification-icon">
                <i class="fas fa-bell"></i>
                <span class="badge">3</span>
            </a>
            <a href="#" class="notification-icon">
                <i class="fas fa-envelope"></i>
                <span class="badge">5</span>
            </a>
        </div>
    </div>
</div>