/**
 * Logic xử lý Sidebar: Hiển thị user info, phân quyền menu, logout
 * Updated: Highlight menu active, Fix hiển thị tên user
 */
document.addEventListener("DOMContentLoaded", function() {
    const userRaw = localStorage.getItem('admin_user');
    const token = localStorage.getItem('admin_token');

    // 1. Check Auth cơ bản Frontend
    if (!token || !userRaw) {
        if (!window.location.pathname.includes('/login')) {
            window.location.href = '/login';
        }
        return;
    }

    const user = JSON.parse(userRaw);
    // Fix: Backend trả về roles là mảng string ['admin', 'manager'] hoặc array object. 
    // Code này giả định roles là mảng string đơn giản.
    const roles = user.roles || []; 

    // 2. Render User Info (FIX: DB dùng full_name)
    const userNameEl = document.getElementById('sidebar-user-name');
    const userRoleEl = document.getElementById('sidebar-user-role');
    
    // Ưu tiên full_name trước, nếu không có thì lấy name, không có nữa thì lấy email
    if(userNameEl) userNameEl.innerText = user.full_name || user.name || user.email || 'Admin';
    if(userRoleEl) userRoleEl.innerText = Array.isArray(roles) ? roles.join(', ').toUpperCase() : 'STAFF';

    // 3. Phân quyền hiển thị Menu
    // Logic: Class .role-admin sẽ ẩn mặc định (d-none), JS này sẽ mở ra nếu đúng role
    if (roles.includes('admin') || roles.includes('super_admin')) {
        // Hiện nhóm Quản lý cửa hàng (trong đó có Sản phẩm)
        document.querySelectorAll('.role-admin').forEach(el => el.classList.remove('d-none'));
        // Admin thấy luôn cả menu vận hành
        document.querySelectorAll('.role-operation').forEach(el => el.classList.remove('d-none'));
    } 
    else if (roles.includes('warehouse') || roles.includes('staff')) {
        // Warehouse chỉ thấy menu vận hành
        document.querySelectorAll('.role-operation').forEach(el => el.classList.remove('d-none'));
    }

    // 4. Highlight Active Menu (Logic Client-side bổ trợ cho Blade)
    // Giúp sidebar sáng đúng mục kể cả khi navigate qua JS
    const currentPath = window.location.pathname; // Ví dụ: /admin/products
    const menuLinks = document.querySelectorAll('.sidebar-item a.sidebar-link');

    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        // Nếu URL trình duyệt chứa href của menu (trừ dashboard để tránh trùng lặp)
        if (href && currentPath.includes(href) && href !== '/admin/dashboard') {
            // Xóa active cũ (nếu có)
            document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));
            // Active cha của link hiện tại
            link.closest('.sidebar-item').classList.add('active');
        }
    });

    // 5. Xử lý Logout
    const logoutBtn = document.getElementById('btn-logout-sidebar');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const handleLogout = () => {
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_user');
                window.location.href = '/login'; // Chuyển hướng về trang login đúng của bạn
            };

            // Gọi API Logout để server xóa token
            if(window.api) {
                // Lưu ý: Route logout chuẩn của Laravel Sanctum thường nằm ở auth hoặc admin prefix
                window.api.post('/api/v1/admin/logout') 
                    .then(() => handleLogout())
                    .catch(() => handleLogout()); // Lỗi network vẫn logout client
            } else {
                handleLogout();
            }
        });
    }
});