const OrderModule = {
    init: function() {
        console.log("Order Module Initialized");
    },

    // Render bảng đơn hàng tại Dashboard
    renderRecentOrders: function(orders) {
        const tbody = document.getElementById('db-recent-orders');
        if (!tbody) return;

        if (!orders || orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">Bạn chưa có đơn hàng nào.</td></tr>';
            return;
        }

        tbody.innerHTML = orders.slice(0, 5).map(order => `
            <tr>
                <td><strong>#${order.code}</strong></td>
                <td>${order.created_at}</td>
                <td>${AppHelpers.formatCurrency(order.total_amount)}</td> <td class="text-center">${AppHelpers.getStatusBadge(order.status, 'order')}</td> <td>
                    <button onclick="OrderModule.viewOrder('${order.id}')" class="btn btn-default btn-xs">
                        <i class="fa fa-eye"></i> Xem
                    </button>
                </td>
            </tr>
        `).join('');
    },

    // Xem chi tiết đơn hàng
    viewOrder: async function(orderId) {
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
                            <strong>Trạng thái:</strong> ${AppHelpers.getStatusBadge(order.status, 'order')}<br>
                            <strong>Thanh toán:</strong> ${AppHelpers.getStatusBadge(order.payment_status, 'payment')}<br>
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
                                <td><strong>${item.product_name}</strong></td>
                                <td class="text-center">${item.quantity}</td>
                                <td class="text-right">${AppHelpers.formatCurrency(item.price)}</td>
                                <td class="text-right">${AppHelpers.formatCurrency(item.total_line)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot>
                        <tr><td colspan="4" class="text-right">Tạm tính:</td><td class="text-right">${AppHelpers.formatCurrency(order.subtotal)}</td></tr>
                        <tr style="font-size: 16px; font-weight: bold; color: #E02222;">
                            <td colspan="4" class="text-right">TỔNG CỘNG:</td><td class="text-right">${AppHelpers.formatCurrency(order.total_amount)}</td>
                        </tr>
                    </tfoot>
                </table>
            `;
        } catch (err) {
            $('#orderDetailModal').modal('hide');
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể tải chi tiết đơn hàng.'
            });
        }
    }
};