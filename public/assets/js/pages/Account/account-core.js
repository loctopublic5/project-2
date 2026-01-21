const AppAccount = {
    // 1. Khởi tạo Dashboard
    init: async function() {
        await this.loadDashboardData();
    },

    // 2. Load dữ liệu tổng hợp
    loadDashboardData: async function() {
        try {
            const [walletRes, ordersRes] = await Promise.all([
                window.api.get('/api/v1/customer/wallet'),
                window.api.get('/api/v1/customer/orders?per_page=10')
            ]);

            if (walletRes.data.status && ordersRes.data.status) {
                const wallet = walletRes.data.data;
                const orders = ordersRes.data.data;

                // Cập nhật số dư
                document.getElementById('db-balance').innerText = this.formatCurrency(parseFloat(wallet.balance));
                
                // Logic Count Đơn hàng đang chờ
                const pendingStatusKeys = ['pending', 'confirmed', 'shipping'];
                const pendingCount = orders.filter(order => 
                    pendingStatusKeys.includes(order.status.key)
                ).length;

                document.getElementById('db-pending').innerText = pendingCount;

                // Render bảng
                this.renderRecentOrders(orders);
            }
        } catch (err) {
            console.error("Dashboard Load Error:", err);
            // Áp dụng SweetAlert cho lỗi tải dữ liệu
            Swal.fire({
                icon: 'error',
                title: 'Lỗi tải dữ liệu',
                text: 'Không thể kết nối với máy chủ. Vui lòng thử lại sau.',
                confirmButtonColor: '#E02222'
            });
        }
    },

    // 3. Render bảng đơn hàng gần đây
    renderRecentOrders: function(orders) {
        const tbody = document.getElementById('db-recent-orders');
        if (!orders || orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">Bạn chưa có đơn hàng nào.</td></tr>';
            return;
        }

        tbody.innerHTML = orders.slice(0, 5).map(order => `
            <tr>
                <td><strong>#${order.code}</strong></td>
                <td>${order.created_at}</td>
                <td>${this.formatCurrency(order.total_amount)}</td>
                <td class="text-center">${this.getStatusBadge(order.status, 'order')}</td>
                <td>
                    <button onclick="AppAccount.viewOrder('${order.id}')" class="btn btn-default btn-xs">
                        <i class="fa fa-eye"></i> Xem
                    </button>
                </td>
            </tr>
        `).join('');
    },

    // 4. Chuyển đổi Tab
    switchTab: function(tabName) {
        $('.sidebar-menu li').removeClass('active');
        $(`.sidebar-menu li[data-tab="${tabName}"]`).addClass('active');

        if(tabName === 'dashboard') this.loadDashboardData();
    },

    // 5. Logout với SweetAlert2
    logout: function() {
        Swal.fire({
            title: 'Xác nhận đăng xuất?',
            text: "Bạn sẽ cần đăng nhập lại để truy cập thông tin cá nhân.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#E02222',
            cancelButtonColor: '#777',
            confirmButtonText: 'Đồng ý, đăng xuất!',
            cancelButtonText: 'Hủy',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Đang xử lý...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const performClientLogout = () => {
                    localStorage.removeItem('token'); 
                    if (window.api && window.api.defaults) {
                        delete window.api.defaults.headers.common['Authorization'];
                    }
                    window.location.href = '/login'; 
                };

                if (window.api) {
                    window.api.post('/api/v1/customer/logout')
                        .then(() => performClientLogout())
                        .catch(() => performClientLogout());
                } else {
                    performClientLogout();
                }
            }
        });
    },

    // 6. Xem chi tiết đơn hàng
    viewOrder: async function(orderId) {
        // Mở Modal và hiển thị loading mặc định
        $('#orderDetailModal').modal('show');
        const container = document.getElementById('order-modal-body');
        container.innerHTML = `<div class="text-center"><i class="fa fa-refresh fa-spin fa-2x"></i></div>`;

        try {
            const res = await window.api.get(`/api/v1/customer/orders/${orderId}`);
            const order = res.data.data;

            document.getElementById('md-order-code').innerText = `#${order.code}`;

            container.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h5><strong>Thông tin nhận hàng</strong></h5>
                        <p>
                            <strong>Người nhận:</strong> ${order.shipping_address.recipient_name}<br>
                            <strong>Điện thoại:</strong> ${order.shipping_address.phone}<br>
                            <strong>Địa chỉ:</strong> ${order.shipping_address.address_detail}
                        </p>
                    </div>
                    <div class="col-md-6 text-right">
                        <h5><strong>Trạng thái & Thanh toán</strong></h5>
                        <p>
                            <strong>Trạng thái:</strong> ${this.getStatusBadge(order.status, 'order')}<br>
                            <strong>Thanh toán:</strong> ${this.getStatusBadge(order.payment_status, 'payment')}<br>
                            <strong>Hình thức:</strong> ${order.payment_method}
                        </p>
                    </div>
                </div>
                <table class="table table-bordered margin-top-20">
                    <thead>
                        <tr class="active">
                            <th colspan="2">Sản phẩm</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-right">Đơn giá</th>
                            <th class="text-right">Tổng</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${order.items.map(item => `
                            <tr>
                                <td width="60"><img src="${item.thumbnail}" width="50" class="img-thumbnail"></td>
                                <td><strong>${item.product_name}</strong><br><small class="text-muted">Option: ${JSON.stringify(item.options)}</small></td>
                                <td class="text-center">${item.quantity}</td>
                                <td class="text-right">${this.formatCurrency(item.price)}</td>
                                <td class="text-right">${this.formatCurrency(item.total_line)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot>
                        <tr><td colspan="4" class="text-right">Tạm tính:</td><td class="text-right">${this.formatCurrency(order.subtotal)}</td></tr>
                        <tr><td colspan="4" class="text-right">Phí vận chuyển:</td><td class="text-right">${this.formatCurrency(order.shipping_fee)}</td></tr>
                        <tr style="font-size: 16px; font-weight: bold; color: #E02222;">
                            <td colspan="4" class="text-right">TỔNG CỘNG:</td><td class="text-right">${this.formatCurrency(order.total_amount)}</td>
                        </tr>
                    </tfoot>
                </table>
            `;
        } catch (err) {
            $('#orderDetailModal').modal('hide'); // Đóng modal nếu lỗi
            Swal.fire({
                icon: 'error',
                title: 'Lỗi lấy chi tiết',
                text: err.response?.data?.message || 'Không thể tải chi tiết đơn hàng.',
                confirmButtonColor: '#E02222'
            });
        }
    },

    getStatusBadge: function(status, type = 'order') {
        if (!status) return '<span class="label label-default">N/A</span>';
        let badgeClass = 'status-badge ';
        if (type === 'order') {
            const map = { 'pending': 'badge-pending', 'confirmed': 'badge-confirmed', 'shipping': 'badge-shipping', 'completed': 'badge-completed', 'cancelled': 'badge-cancelled' };
            badgeClass += map[status.key] || 'label-default';
        } else {
            badgeClass += (status.key === 'paid') ? 'badge-paid' : 'badge-unpaid';
        }
        return `<span class="${badgeClass}">${status.label}</span>`;
    },

    formatCurrency: function(value) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    }
};