const UserProfileModule = {
    // 1. Lấy ID từ URL (VD: domain.com/customer/user/15 -> lấy 15)
    getUserId: function() {
        const pathSegments = window.location.pathname.split('/');
        return pathSegments[pathSegments.length - 1];
    },

    formatCurrency: (amount) => new Intl.NumberFormat('vi-VN', { 
        style: 'currency', 
        currency: 'VND' 
    }).format(amount || 0),

    init: function() {
        this.loadUserDetail();
    },

    loadUserDetail: async function() {
        const id = this.getUserId();
        if (!id || isNaN(id)) return;

        try {
            const res = await window.api.get(`/api/v1/customer/user/${id}`);
            const user = res.data.data;

            // Đổ dữ liệu thông tin cơ bản
            $('#detail-name').text(user.full_name || '---');
            $('#detail-email').text(user.email);
            $('#detail-phone').text(user.phone || 'Chưa cập nhật');
            $('#detail-joined').text(user.joined_at || '---');
            
            if(user.avatar_url) {
                $('#detail-avatar').attr('src', user.avatar_url);
            }

            // Status Badge
            const statusHtml = user.is_active 
                ? '<i class="bi bi-shield-check fs-4"></i> <span class="ms-3 badge bg-success">Hoạt động</span>'
                : '<i class="bi bi-shield-x fs-4"></i> <span class="ms-3 badge bg-danger">Bị khóa</span>';
            $('#detail-status').html(statusHtml);

            // Ví & Rank
            const balance = user.vip_info ? user.vip_info.wallet_balance : 0;
            $('#detail-wallet').text(this.formatCurrency(balance));
            $('#detail-rank').text(user.vip_info ? user.vip_info.rank : 'Thành viên');

            // Render Sổ địa chỉ
            this.renderAddresses(user.addresses);

            // Render Đơn hàng (Dùng recent_orders từ Resource)
            this.renderOrders(user.recent_orders);

        } catch (error) {
            console.error("Lỗi profile:", error);
            $('#address-list, #order-list').html('<tr><td colspan="4" class="text-center text-danger">Không thể tải dữ liệu</td></tr>');
        }
    },

    renderAddresses: function(addresses) {
        const tbody = $('#address-list');
        if (!addresses || addresses.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center text-muted">Chưa có địa chỉ</td></tr>');
            return;
        }

        const html = addresses.map(addr => `
            <tr>
                <td>${addr.recipient_name}</td>
                <td>${addr.phone}</td>
                <td><small>${addr.address_detail || addr.full_address || ''}</small></td>
                <td>${addr.is_default ? '<span class="badge bg-info">Mặc định</span>' : ''}</td>
            </tr>
        `).join('');
        tbody.html(html);
    },

    renderOrders: function(orders) {
        const tbody = $('#order-list');
        if (!orders || orders.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center text-muted">Chưa có đơn hàng</td></tr>');
            return;
        }

        const html = orders.map(order => {
            let statusClass = 'bg-secondary';
            if(order.status === 'completed') statusClass = 'bg-success';
            if(order.status === 'pending') statusClass = 'bg-warning';
            if(order.status === 'cancelled') statusClass = 'bg-danger';

            return `
                <tr>
                    <td><a href="/customer/orders/${order.id}" class="font-bold">#${order.code || order.id}</a></td>
                    <td>${order.created_at}</td>
                    <td class="font-bold">${this.formatCurrency(order.total_amount)}</td>
                    <td><span class="badge ${statusClass}">${order.status}</span></td>
                </tr>
            `;
        }).join('');
        tbody.html(html);
    }
};
