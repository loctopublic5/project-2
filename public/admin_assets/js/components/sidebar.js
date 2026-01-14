/**
 * Logic xử lý Sidebar: Hiển thị user info, phân quyền menu, logout
 */
document.addEventListener("DOMContentLoaded", function() {
    const userRaw = localStorage.getItem('admin_user');
    const token = localStorage.getItem('admin_token');

    // 1. Check Auth cơ bản Frontend
    if (!token || !userRaw) {
        // Nếu đang không ở trang login thì đá về login
        if (!window.location.pathname.includes('/login')) {
            window.location.href = '/login';
        }
        return;
    }

    const user = JSON.parse(userRaw);
    const roles = user.roles || []; 

    // 2. Render User Info
    const userNameEl = document.getElementById('sidebar-user-name');
    const userRoleEl = document.getElementById('sidebar-user-role');
    
    if(userNameEl) userNameEl.innerText = user.name || 'Admin';
    if(userRoleEl) userRoleEl.innerText = Array.isArray(roles) ? roles.join(', ').toUpperCase() : 'STAFF';

    // 3. Phân quyền hiển thị Menu
    if (roles.includes('admin') || roles.includes('super_admin')) {
        document.querySelectorAll('.role-admin').forEach(el => el.classList.remove('d-none'));
        document.querySelectorAll('.role-operation').forEach(el => el.classList.remove('d-none'));
    } else if (roles.includes('warehouse')) {
        document.querySelectorAll('.role-operation').forEach(el => el.classList.remove('d-none'));
    }

    // 4. Xử lý Logout
    const logoutBtn = document.getElementById('btn-logout-sidebar');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const handleLogout = () => {
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_user');
                window.location.href = '/login';
            };

            if(window.api) {
                window.api.post('/api/v1/auth/logout')
                    .then(() => handleLogout())
                    .catch(() => handleLogout()); // Lỗi cũng logout luôn
            } else {
                handleLogout();
            }
        });
    }
});