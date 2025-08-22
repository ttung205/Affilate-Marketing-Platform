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
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Quản lý sản phẩm</span>
                        </li>
                        @if(request()->routeIs('admin.products.index'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Tất cả sản phẩm</span>
                            </li>
                        @elseif(request()->routeIs('admin.products.create'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Thêm sản phẩm</span>
                            </li>
                        @elseif(request()->routeIs('admin.products.edit'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Sửa sản phẩm</span>
                            </li>
                        @elseif(request()->routeIs('admin.products.show'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Chi tiết sản phẩm</span>
                            </li>
                        @endif
                    @elseif(request()->routeIs('admin.categories.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Quản lý sản phẩm</span>
                        </li>
                        @if(request()->routeIs('admin.categories.index'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Danh mục</span>
                            </li>
                        @elseif(request()->routeIs('admin.categories.create'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Thêm danh mục</span>
                            </li>
                        @elseif(request()->routeIs('admin.categories.edit'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Sửa danh mục</span>
                            </li>
                        @endif
                    @elseif(request()->routeIs('admin.users.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Quản lý người dùng</span>
                        </li>
                        @if(request()->routeIs('admin.users.index'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Tất cả người dùng</span>
                            </li>
                        @elseif(request()->routeIs('admin.users.create'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Thêm người dùng</span>
                            </li>
                        @elseif(request()->routeIs('admin.users.edit'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Sửa người dùng</span>
                            </li>
                        @elseif(request()->routeIs('admin.users.show'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Chi tiết người dùng</span>
                            </li>
                        @endif
                    @elseif(request()->routeIs('admin.affiliate-links.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Quản lý Affiliate Links</span>
                        </li>
                        @if(request()->routeIs('admin.affiliate-links.index'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Tất cả Affiliate Links</span>
                            </li>
                        @elseif(request()->routeIs('admin.affiliate-links.create'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Tạo Affiliate Link</span>
                            </li>
                        @elseif(request()->routeIs('admin.affiliate-links.edit'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Sửa Affiliate Link</span>
                            </li>
                        @endif
                    @elseif(request()->routeIs('admin.campaigns.*'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item">
                            <span>Quản lý Campaigns</span>
                        </li>
                        @if(request()->routeIs('admin.campaigns.index'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Tất cả Campaigns</span>
                            </li>
                        @elseif(request()->routeIs('admin.campaigns.create'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Tạo Campaign</span>
                            </li>
                        @elseif(request()->routeIs('admin.campaigns.edit'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Sửa Campaign</span>
                            </li>
                        @elseif(request()->routeIs('admin.campaigns.show'))
                            <li class="breadcrumb-item">
                                <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                            </li>
                            <li class="breadcrumb-item active">
                                <span>Chi tiết Campaign</span>
                            </li>
                        @endif
                    @elseif(request()->routeIs('admin.dashboard'))
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
                        </li>
                        <li class="breadcrumb-item active">
                            <span>Tổng quan</span>
                        </li>
                    @else
                        <li class="breadcrumb-item">
                            <i class="fas fa-chevron-right breadcrumb-arrow"></i>
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