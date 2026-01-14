/**
 * JS Logic cho trang Quản lý User
 * Updated: Map đúng với UserResource mới
 */

const API_ANALYTICS = '/api/v1/admin/users/analytics';
const API_LIST = '/api/v1/admin/users';

const formatCurrency = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);

// --- 1. Load Analytics ---
const loadAnalytics = async () => {
    try {
        const res = await window.api.get(API_ANALYTICS);
        
        // FIX 1: API trả về { status: true, data: { stats: ... } }
        // Axios bọc 1 lớp data -> res.data
        // Laravel trả về key data -> res.data.data
        // Vậy nên đường dẫn đúng là: res.data.data.stats
        const stats = res.data.data.stats; 

        if(stats) {
            document.getElementById('stat-total').innerText = stats.total || 0;
            document.getElementById('stat-active').innerText = stats.active || 0;
            document.getElementById('stat-banned').innerText = stats.banned || 0;
            document.getElementById('stat-new').innerText = stats.new_this_month || 0;
        }
    } catch (error) { 
        console.error("Lỗi analytics", error); 
    }
}

// --- 2. Load Users Table ---
const loadUsers = async (page = 1) => {
    const keyword = document.getElementById('filter-keyword').value;
    const status = document.getElementById('filter-status').value;
    const sortSpending = document.getElementById('sort-spending').value;

    const tbody = document.getElementById('user-list-body');
    const paginationDiv = document.getElementById('pagination-links');
    
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';

    try {
        const res = await window.api.get(API_LIST, {
            params: { page, keyword, status, sort_spending: sortSpending }
        });
        
        // Laravel Resource Collection trả về data trực tiếp trong res.data.data
        const users = res.data.data;
        const meta = res.data.meta;

        renderTable(users);
        renderPagination(meta);

    } catch (error) {
        console.error(error);
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>';
        paginationDiv.innerHTML = '';
    }
}

// --- 3. Render Table HTML ---
const renderTable = (users) => {
    const tbody = document.getElementById('user-list-body');
    tbody.innerHTML = '';

    if(!users || users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Không tìm thấy khách hàng nào</td></tr>';
        return;
    }

    users.forEach(u => {
        // FIX 2: Map đúng key từ UserResource
        const avatarUrl = u.avatar ? u.avatar : '/assets/compiled/jpg/1.jpg'; // Resource đã xử lý url
        const displayName = u.full_name || u.email; // Resource trả về full_name
        
        // FIX 3: Lấy dữ liệu VIP từ object vip_info
        const walletBalance = u.vip_info ? u.vip_info.wallet_balance : 0;
        const totalSpending = u.vip_info ? u.vip_info.total_spending : 0;
        const rankName = u.vip_info ? u.vip_info.rank : 'Member';

        // Badge Role
        const roleBadge = u.roles.includes('Admin') 
            ? '<span class="badge bg-primary">Admin</span>' 
            : '<span class="badge bg-secondary">Customer</span>';
        
        // Badge Status
        let statusBadge = u.is_active 
            ? '<span class="badge bg-success">Active</span>' 
            : '<span class="badge bg-danger">Blocked</span>';

        // Badge Rank (Optional - làm màu cho đẹp)
        let rankBadge = '';
        if(rankName === 'Diamond') rankBadge = '<span class="badge bg-info ms-1">💎</span>';
        if(rankName === 'Gold') rankBadge = '<span class="badge bg-warning ms-1">🥇</span>';

        const row = `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md">
                            <img src="${avatarUrl}" alt="avt" style="object-fit: cover;">
                        </div>
                        <div class="ms-3 name">
                            <h6 class="font-bold mb-0">
                                <a href="/admin/users/${u.id}">${displayName}</a>
                                ${rankBadge}
                            </h6>
                            <span class="text-muted fs-7">${u.email}</span>
                        </div>
                    </div>
                </td>
                <td>${roleBadge}</td>
                <td class="text-end text-success font-bold">${formatCurrency(walletBalance)}</td>
                <td class="text-end text-primary font-bold">${formatCurrency(totalSpending)}</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center">
                    <a href="/admin/users/${u.id}" class="btn btn-sm btn-info" title="Xem chi tiết"><i class="bi bi-eye"></i></a>
                    <button class="btn btn-sm btn-warning" onclick="toggleStatus(${u.id}, ${u.is_active})" title="Khóa/Mở"><i class="bi bi-lock"></i></button>
                </td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', row);
    });
}

// --- 4. Render Pagination (Giữ nguyên) ---
const renderPagination = (meta) => {
    const paginationDiv = document.getElementById('pagination-links');
    if (!meta || meta.last_page <= 1) {
        paginationDiv.innerHTML = '';
        return;
    }

    let html = '<nav><ul class="pagination pagination-primary justify-content-center">';
    
    // Previous
    html += `<li class="page-item ${meta.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(${meta.current_page - 1})">‹</a>
             </li>`;

    // Pages Logic
    let start = Math.max(1, meta.current_page - 2);
    let end = Math.min(meta.last_page, meta.current_page + 2);

    if (start > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(1)">1</a></li>`;
            if (start > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
    }

    for (let i = start; i <= end; i++) {
        html += `<li class="page-item ${i === meta.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(${i})">${i}</a>
                 </li>`;
    }

    if (end < meta.last_page) {
            if (end < meta.last_page - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(${meta.last_page})">${meta.last_page}</a></li>`;
    }

    // Next
    html += `<li class="page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(${meta.current_page + 1})">›</a>
             </li>`;

    html += '</ul></nav>';
    paginationDiv.innerHTML = html;
}

// --- 5. Toggle Status (Giữ nguyên) ---
window.toggleStatus = (id, currentStatus) => {
    const action = currentStatus == 1 ? "khóa" : "mở khóa";
    Swal.fire({
        title: `Xác nhận ${action}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await window.api.patch(`/api/v1/admin/users/${id}/status`);
                Swal.fire('Thành công', '', 'success');
                loadUsers(); 
                loadAnalytics();
            } catch (e) {
                Swal.fire('Lỗi', e.response?.data?.message || 'Lỗi server', 'error');
            }
        }
    })
}

// --- Init ---
document.addEventListener('DOMContentLoaded', () => {
    loadAnalytics();
    loadUsers(1);

    document.getElementById('btn-filter').addEventListener('click', () => loadUsers(1));
});