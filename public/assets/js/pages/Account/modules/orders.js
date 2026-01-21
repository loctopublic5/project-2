const OrderModule = {
    currentStatus: '',

    init: function() {
        console.log("Order Module Initialized");
        // Nếu đang ở Tab Đơn hàng thì mới tự động load
        if ($('#tab-orders').is(':visible')) {
            this.loadOrders();
        }
    },

    // --- LOGIC CHO TAB ĐƠN HÀNG (QUẢN LÝ TẬP TRUNG) ---
    filter: function(status) {
        this.currentStatus = status;
        $('.order-tabs li').removeClass('active');
        $(`.order-tabs li:has(a[onclick*="'${status}'"])`).addClass('active');
        this.loadOrders();
    },

    loadOrders: async function() {
        const listArea = $('#order-history-list');
        listArea.html('<div class="text-center padding-v-20"><i class="fa fa-refresh fa-spin fa-2x"></i></div>');

        try {
            const res = await window.api.get('/api/v1/customer/orders', { 
                params: { status: this.currentStatus } 
            });
            const orders = res.data.data;

            if (orders.length === 0) {
                listArea.html('<div class="well text-center margin-top-20">Không có đơn hàng nào trong trạng thái này.</div>');
                return;
            }

            listArea.html(orders.map(order => this.renderOrderCard(order)).join(''));
        } catch (err) {
            listArea.html('<div class="alert alert-danger">Không thể tải danh sách đơn hàng.</div>');
        }
    },

    // --- RENDER CARD STYLE (SHOPEE STYLE) ---
    renderOrderCard: function(order) {
        return `
            <div class="order-item-box panel panel-default margin-bottom-20">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-7">
                            <span class="bold text-primary">#${order.code}</span>
                            <span class="text-muted hidden-xs"> | ${order.created_at}</span>
                        </div>
                        <div class="col-xs-5 text-right">
                            ${AppHelpers.getStatusBadge(order.status, 'order')}
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    ${order.items.map(item => `
                        <div class="product-line flex-row mb-10">
                            <img src="${item.thumbnail}" width="60" class="img-thumbnail" onerror="this.src='/assets/no-image.jpg'">
                            <div class="flex-grow-1 ml-15">
                                <div class="bold font-15">${item.product_name}</div>
                                <div class="text-muted small">Phân loại: ${item.options || 'Mặc định'}</div>
                                <div class="margin-top-5">
                                    <span class="text-danger">${AppHelpers.formatCurrency(item.price)}</span>
                                    <span class="pull-right text-muted">x${item.quantity}</span>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                    <hr class="margin-v-10">
                    <div class="text-right">
                        <span class="text-muted">Tổng số tiền: </span>
                        <span class="text-danger bold font-18">${AppHelpers.formatCurrency(order.total_amount)}</span>
                    </div>
                </div>
                <div class="panel-footer text-right">
                    ${this.renderActionButtons(order)}
                </div>
            </div>
        `;
    },

    // --- LOGIC NÚT BẤM DỰA TRÊN TRẠNG THÁI ---
    renderActionButtons: function(order) {
        const key = order.status.key;
        let btns = `<button onclick="OrderModule.viewOrder('${order.id}')" class="btn btn-default btn-sm mr-5"><i class="fa fa-eye"></i> Xem chi tiết</button>`;

        if (key === 'pending') {
            btns += `<button onclick="OrderModule.cancelOrder('${order.id}')" class="btn btn-danger btn-sm">Hủy đơn</button>`;
        } else if (key === 'shipping') {
            btns += `<button onclick="OrderModule.confirmReceived('${order.id}')" class="btn btn-primary btn-sm">Đã nhận hàng</button>`;
        } else if (key === 'completed') {
            btns += `<button onclick="OrderModule.showReviewModal('${order.id}')" class="btn btn-warning btn-sm"><i class="fa fa-star"></i> Đánh giá</button>`;
        }

        return btns;
    },

    // --- RE-USE DASHBOARD LOGIC (BẢNG RÚT GỌN) ---
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
                <td>${AppHelpers.formatCurrency(order.total_amount)}</td>
                <td class="text-center">${AppHelpers.getStatusBadge(order.status, 'order')}</td>
                <td class="text-right">
                    <button onclick="OrderModule.viewOrder('${order.id}')" class="btn btn-default btn-xs">
                        <i class="fa fa-eye"></i> Xem
                    </button>
                </td>
            </tr>
        `).join('');
    },

    // --- CHI TIẾT ĐƠN HÀNG (MODAL CHUNG) ---
    viewOrder: async function(orderId) {
        $('#orderDetailModal').modal('show');
        const container = $('#order-modal-body');
        container.html(`<div class="text-center padding-v-20"><i class="fa fa-refresh fa-spin fa-2x"></i></div>`);

        try {
            const res = await window.api.get(`/api/v1/customer/orders/${orderId}`);
            const order = res.data.data;

            $('#md-order-code').text(`#${order.code}`);

            container.html(`
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="bold"><i class="fa fa-truck"></i> Thông tin nhận hàng</h5>
                        <div class="well well-sm">
                            <strong>${order.shipping_address.recipient_name}</strong><br>
                            ${order.shipping_address.phone}<br>
                            ${order.shipping_address.address_detail}
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <h5 class="bold">Trạng thái đơn hàng</h5>
                        <p>${AppHelpers.getStatusBadge(order.status, 'order')}</p>
                        <p><strong>Thanh toán:</strong> ${order.payment_method}</p>
                    </div>
                </div>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr class="active">
                            <th>Sản phẩm</th>
                            <th class="text-center">SL</th>
                            <th class="text-right">Đơn giá</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${order.items.map(item => `
                            <tr>
                                <td>${item.product_name}</td>
                                <td class="text-center">${item.quantity}</td>
                                <td class="text-right">${AppHelpers.formatCurrency(item.price)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot>
                        <tr><td colspan="2" class="text-right bold">Tổng cộng:</td><td class="text-right text-danger bold">${AppHelpers.formatCurrency(order.total_amount)}</td></tr>
                    </tfoot>
                </table>
            `);
        } catch (err) {
            Swal.fire('Lỗi', 'Không thể tải chi tiết đơn hàng', 'error');
        }
    },

    // --- HÀNH ĐỘNG: HỦY ĐƠN ---
    cancelOrder: function(id) {
        Swal.fire({
            title: 'Hủy đơn hàng này?',
            text: "Vui lòng cho biết lý do hủy đơn:",
            input: 'text',
            inputPlaceholder: 'Lý do (không bắt buộc)',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Xác nhận hủy'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    await window.api.put(`/api/v1/customer/orders/${id}/cancel`, { reason: result.value });
                    Swal.fire('Đã hủy', 'Đơn hàng của bạn đã được hủy thành công.', 'success');
                    this.loadOrders();
                    // Nếu Dashboard đang mở thì cập nhật lại luôn
                    if(typeof DashboardModule !== 'undefined') DashboardModule.init(); 
                } catch (err) {
                    Swal.fire('Thất bại', err.response?.data?.message || 'Không thể hủy đơn', 'error');
                }
            }
        });
    },

    confirmReceived: function(id) {
    console.log("Đang kích hoạt xác nhận cho đơn hàng ID:", id); // Dòng này để kiểm tra xem nút có ăn click không

    Swal.fire({
        title: 'Xác nhận nhận hàng?',
        text: "Bạn xác nhận đã nhận đủ hàng và thanh toán cho đơn hàng này?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Đã nhận hàng!',
        cancelButtonText: 'Hủy'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                Swal.showLoading();
                // Gọi API Patch mà bạn đã viết ở Backend
                await window.api.patch(`/api/v1/customer/orders/${id}/confirm`);
                
                Swal.fire(
                    'Thành công!',
                    'Đơn hàng đã chuyển sang trạng thái Hoàn thành.',
                    'success'
                );
                
                // Load lại danh sách để cập nhật UI
                this.loadOrders(); 
                if(typeof DashboardModule !== 'undefined') DashboardModule.init();
            } catch (err) {
                console.error(err);
                Swal.fire('Lỗi', err.response?.data?.message || 'Không thể xác nhận đơn hàng', 'error');
            }
        }
    });
},

};