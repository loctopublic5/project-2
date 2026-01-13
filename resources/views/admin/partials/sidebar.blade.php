<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="{{ url('/admin/dashboard') }}">
                        <img src="{{ asset('admin_assets/assets/compiled/svg/logo.svg') }}" alt="Logo">
                    </a>
                </div>
                <div class="sidebar-toggler x">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>

        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-title">Menu</li>

                {{-- 1. DASHBOARD (Ai cũng thấy) --}}
                <li class="sidebar-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <a href="{{ url('/admin/dashboard') }}" class='sidebar-link'>
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- CHECK LOGIN TRƯỚC ĐỂ TRÁNH LỖI --}}
                @if(auth()->check())

                    {{-- 2. NHÓM QUẢN LÝ (Chỉ role 'admin' thấy) --}}
                    @if(auth()->user()->hasRole('admin'))
                        <li class="sidebar-title">Quản lý cửa hàng</li>

                        <li class="sidebar-item {{ request()->is('admin/products*') ? 'active' : '' }}">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-box-seam"></i>
                                <span>Sản phẩm</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('admin/categories*') ? 'active' : '' }}">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-tags-fill"></i>
                                <span>Danh mục</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('admin/orders*') ? 'active' : '' }}">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-cart-check-fill"></i>
                                <span>Duyệt đơn hàng</span>
                            </a>
                        </li>
                    @endif

                    {{-- 3. NHÓM VẬN HÀNH (Cả 'admin' và 'warehouse' đều thấy) --}}
                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('warehouse'))
                        <li class="sidebar-title">Vận hành & Kho</li>

                        <li class="sidebar-item {{ request()->is('admin/shipments*') ? 'active' : '' }}">
                            <a href="#" class='sidebar-link'>
                                <i class="bi bi-truck"></i>
                                <span>Vận đơn (Giao hàng)</span>
                            </a>
                        </li>
                    @endif

                @endif

                {{-- LOGOUT --}}
                <li class="sidebar-item mt-4">
                    <a href="#" id="btn-logout" class='sidebar-link'>
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Đăng xuất</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>