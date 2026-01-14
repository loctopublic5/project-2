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
                
                {{-- 1. PROFILE WIDGET (Hiển thị tên Admin đang đăng nhập) --}}
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

                {{-- DASHBOARD (Ai cũng thấy) --}}
                <li class="sidebar-item {{ request()->is('admin/dashboard*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/dashboard') }}" class='sidebar-link'>
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- 2. NHÓM QUẢN LÝ CỬA HÀNG --}}
                {{-- Mặc định thêm class 'd-none' và 'role-admin' để JS xử lý --}}
                
                <li class="sidebar-title role-admin d-none">Quản lý Cửa hàng</li>

                <li class="sidebar-item role-admin d-none {{ request()->is('admin/categories*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/categories') }}" class='sidebar-link'>
                        <i class="bi bi-tags-fill"></i>
                        <span>Danh mục</span>
                    </a>
                </li>

                <li class="sidebar-item role-admin d-none {{ request()->is('admin/products*') ? 'active' : '' }}">
                    <a href="#" class='sidebar-link'>
                        <i class="bi bi-box-seam"></i>
                        <span>Sản phẩm</span>
                    </a>
                </li>

                <li class="sidebar-item role-admin d-none {{ request()->is('admin/users*') ? 'active' : '' }}">
                    <a href="{{ url('/admin/users') }}" class='sidebar-link'>
                        <i class="bi bi-people-fill"></i>
                        <span>Khách hàng</span>
                            {{-- Badge user mới nếu cần --}}
                            <span class="badge bg-danger ms-auto d-none" id="new-user-badge">New</span>
                    </a>
                </li>

                {{-- 3. NHÓM VẬN HÀNH (Admin + Warehouse thấy) --}}
                <li class="sidebar-title role-operation d-none">Vận hành & Đơn hàng</li>

                <li class="sidebar-item role-operation d-none {{ request()->is('admin/orders*') ? 'active' : '' }}">
                    <a href="#" class='sidebar-link'>
                        <i class="bi bi-cart-check-fill"></i>
                        <span>Đơn hàng</span>
                    </a>
                </li>

                <li class="sidebar-item role-operation d-none {{ request()->is('admin/shipments*') ? 'active' : '' }}">
                    <a href="#" class='sidebar-link'>
                        <i class="bi bi-truck"></i>
                        <span>Vận chuyển</span>
                    </a>
                </li>

                {{-- LOGOUT --}}
                <li class="sidebar-item mt-4">
                    <form action="{{ url('/logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="sidebar-link btn w-100 text-start border-0 bg-transparent">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Đăng xuất</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

{{-- SCRIPT XỬ LÝ HIỂN THỊ MENU TỪ LOCALSTORAGE --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Lấy thông tin từ LocalStorage
        const userRaw = localStorage.getItem('admin_user');
        const token = localStorage.getItem('admin_token');

        // Nếu không có token -> Đá về login ngay (bảo mật frontend)
        if (!token || !userRaw) {
            window.location.href = '/admin/login';
            return;
        }

        const user = JSON.parse(userRaw);
        const roles = user.roles || []; // Ví dụ: ["admin"] hoặc ["warehouse"]

        // 2. Hiển thị thông tin user lên sidebar
        document.getElementById('sidebar-user-name').innerText = user.name || user.full_name || 'Admin';
        document.getElementById('sidebar-user-role').innerText = roles.join(', ').toUpperCase();

        // 3. LOGIC PHÂN QUYỀN HIỂN THỊ MENU
        
        // CASE A: Nếu là ADMIN hoặc SUPER_ADMIN -> Hiện tất cả menu admin
        if (roles.includes('admin') || roles.includes('super_admin')) {
            // Hiện nhóm Admin
            document.querySelectorAll('.role-admin').forEach(el => el.classList.remove('d-none'));
            // Hiện nhóm Vận hành (Admin cũng cần xem đơn)
            document.querySelectorAll('.role-operation').forEach(el => el.classList.remove('d-none'));
        }

        // CASE B: Nếu là WAREHOUSE (Thủ kho) -> Chỉ hiện nhóm Vận hành
        else if (roles.includes('warehouse')) {
            document.querySelectorAll('.role-operation').forEach(el => el.classList.remove('d-none'));
        }

        // 4. Xử lý Đăng xuất (Logout)
        document.getElementById('btn-logout-sidebar').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Xóa LocalStorage
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_user');
            
            // Gọi API Logout (Optional - để hủy token trên server)
            if(window.api) {
                window.api.post('/api/v1/auth/logout').finally(() => {
                    window.location.href = '/admin/login';
                });
            } else {
                window.location.href = '/admin/login';
            }
        });
    });
</script>