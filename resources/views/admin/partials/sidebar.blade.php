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
                {{-- PROFILE WIDGET --}}
                <li class="sidebar-item">
                    <div class="d-flex align-items-center px-3 py-2">
                        <div class="avatar avatar-md me-2">
                            <img src="{{ asset('admin_assets/assets/compiled/jpg/1.jpg') }}" alt="Avatar">
                        </div>
                        <div>
                            <div class="fw-bold" id="sidebar-user-name">Đang tải...</div>
                            <div class="text-xs text-muted" id="sidebar-user-role">...</div>
                        </div>
                    </div>
                </li>

                <li class="sidebar-title">Menu Chính</li>

                <li class="sidebar-item {{ request()->is('admin/dashboard*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/dashboard') }}" class='sidebar-link'>
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- NHÓM QUẢN LÝ CỬA HÀNG (Admin only) --}}
                <li class="sidebar-title role-admin d-none">Quản lý Cửa hàng</li>

                <li class="sidebar-item role-admin d-none {{ request()->is('admin/categories*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/categories') }}" class='sidebar-link'>
                        <i class="bi bi-tags-fill"></i>
                        <span>Danh mục</span>
                    </a>
                </li>

                {{-- Sửa: Thêm role-admin và logic active cho Users --}}
                <li class="sidebar-item role-admin d-none {{ request()->is('admin/users*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/users') }}" class='sidebar-link'>
                        <i class="bi bi-people-fill"></i>
                        <span>Khách hàng</span>
                    </a>
                </li>

                {{-- NHÓM VẬN HÀNH --}}
                <li class="sidebar-title role-operation d-none">Vận hành</li>

                <li class="sidebar-item role-operation d-none {{ request()->is('admin/orders*') ? 'active' : '' }}">
                    <a href="#" class='sidebar-link'>
                        <i class="bi bi-cart-check-fill"></i>
                        <span>Đơn hàng</span>
                    </a>
                </li>

                {{-- LOGOUT --}}
                <li class="sidebar-item mt-4">
                    {{-- Sửa: Thêm ID để JS bắt sự kiện --}}
                    <a href="#" id="btn-logout-sidebar" class="sidebar-link btn w-100 text-start border-0 bg-transparent text-danger">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Đăng xuất</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
{{-- Logic: Nếu URL bắt đầu bằng admin/dashboard thì active --}}
<li class="sidebar-item {{ request()->is('admin/dashboard*') ? 'active' : '' }}">
    <a href="{{ url('/admin/dashboard') }}" class='sidebar-link'>
        <i class="bi bi-grid-fill"></i>
        <span>Dashboard</span>
    </a>
</li>

{{-- Logic: Nếu URL bắt đầu bằng admin/users thì active --}}
<li class="sidebar-item {{ request()->is('admin/users*') ? 'active' : '' }}">
    <a href="{{ url('/admin/users') }}" class='sidebar-link'>
        <i class="bi bi-people-fill"></i>
        <span>Khách hàng</span>
    </a>
</li>

<script src="{{ asset('admin_assets/js/components/sidebar.js') }}"></script>