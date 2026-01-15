/**
 * JS Logic cho trang Chi tiết User (Đã update theo Resource mới)
 */
const pathSegments = window.location.pathname.split('/');
const userId = pathSegments[pathSegments.length - 1];
const API_DETAIL = `/api/v1/admin/users/${userId}`;

const formatCurrency = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);

const loadUserDetail = async () => {
    try {
        const res = await window.api.get(API_DETAIL);
        const user = res.data.data; // Lấy cục data từ Resource

        // 1. Fill Info Basic
        // Resource trả về 'full_name', JS cũ gọi 'name' -> Cần sửa
        const nameEl = document.getElementById('detail-name');
        if(nameEl) nameEl.innerText = user.full_name || user.name || '---';
        
        document.getElementById('detail-email').innerText = user.email;
        document.getElementById('detail-phone').innerText = user.phone || 'Chưa cập nhật';
        
        // Resource đã trả về string 'joined_at' định dạng sẵn (dd/mm/YYYY)
        // Không cần dùng hàm formatDate nữa
        document.getElementById('detail-joined').innerText = user.joined_at; 
        
        // Avatar (Resource trả về avatar hoặc null)
        if(user.avatar) {
            document.getElementById('detail-avatar').src = user.avatar; // Resource đã xử lý url
        }

        // Status
        const statusEl = document.getElementById('detail-status');
        if (user.is_active) {
            statusEl.innerHTML = '<span class="badge bg-success">Hoạt động</span>';
        } else {
            statusEl.innerHTML = '<span class="badge bg-danger">Bị khóa</span>';
        }

        // 2. Fill Wallet & Rank (FIX QUAN TRỌNG)
        // JSON mới gom vào object 'vip_info'
        const walletBalance = user.vip_info ? user.vip_info.wallet_balance : 0;
        document.getElementById('detail-wallet').innerText = formatCurrency(walletBalance);
        
        // Nếu có hiển thị Rank
        if(document.getElementById('detail-rank') && user.vip_info) {
             document.getElementById('detail-rank').innerText = user.vip_info.rank;
        }

        // 3. Fill Addresses
        const addressBody = document.getElementById('address-list');
        if(addressBody) {
            addressBody.innerHTML = '';
            if (user.addresses && user.addresses.length > 0) {
                user.addresses.forEach(addr => {
                    const isDefault = addr.is_default ? '<span class="badge bg-info">Mặc định</span>' : '';
                    addressBody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td>${addr.recipient_name || user.full_name}</td>
                            <td>${addr.phone}</td>
                            <td>${addr.address_detail || ''}</td>
                            <td>${isDefault}</td>
                        </tr>
                    `);
                });
            } else {
                addressBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Chưa có địa chỉ nào</td></tr>';
            }
        }

        // 4. Fill Orders (Resource gọi là 'recent_orders')
        const orderBody = document.getElementById('order-list');
        if(orderBody) {
            orderBody.innerHTML = '';
            // Resource trả về 'recent_orders' chứ không phải 'orders'
            const orders = user.recent_orders || []; 
            
            if (orders.length > 0) {
                orders.forEach(order => {
                    let statusClass = 'bg-secondary';
                    if(order.status === 'completed') statusClass = 'bg-success';
                    if(order.status === 'pending') statusClass = 'bg-warning';
                    if(order.status === 'cancelled') statusClass = 'bg-danger';

                    // Xử lý ngày tháng đơn hàng (vẫn cần format vì order không qua resource riêng)
                    const date = new Date(order.created_at).toLocaleDateString('vi-VN');

                    orderBody.insertAdjacentHTML('beforeend', `
                        <tr>
                            <td><a href="#">#${order.code || order.id}</a></td>
                            <td>${date}</td>
                            <td class="font-bold">${formatCurrency(order.total_amount)}</td>
                            <td><span class="badge ${statusClass}">${order.status}</span></td>
                        </tr>
                    `);
                });
            } else {
                orderBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Chưa phát sinh đơn hàng</td></tr>';
            }
        }

    } catch (error) {
        console.error("Lỗi tải chi tiết user:", error);
        // Alert lỗi nhẹ nhàng thôi
        // Swal.fire('Thông báo', 'Không thể tải dữ liệu chi tiết.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    loadUserDetail();
});