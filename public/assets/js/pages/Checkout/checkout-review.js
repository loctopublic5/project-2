Checkout.OrderReview = (function () {
    return {
        initReview: async function () {
            const $tableBody = $('#table-confirm-order tbody');
            const $summaryUl = $('#checkout-final-summary');

            try {
                // 1. GỬI KÈM address_id ĐỂ SERVER TÍNH PHÍ SHIP
                const res = await window.api.get('/api/v1/customer/cart', {
                    params: {
                        address_id: Checkout.data.selectedAddressId
                    }
                });
                
                const cartData = res.data.data;

                // 2. Render danh sách sản phẩm
                let itemsHtml = '';
                cartData.items.forEach(item => {
                    const p = item.product_info;
                    
                    // XỬ LÝ HIỂN THỊ ẢNH (Phòng trường hợp server trả về path thiếu domain)
                    const avatarHtml = `<img src="${p.avatar}" alt="${p.name}" class="img-responsive" style="max-width: 80px;">`;

                    itemsHtml += `
                        <tr class="${item.is_error ? 'item-error' : ''}">
                            <td class="checkout-image">${avatarHtml}</td>
                            <td class="checkout-description">
                                <h3><a href="javascript:;">${p.name}</a></h3>
                                <p>${item.is_error ? `<span class="text-danger">${item.error_message}</span>` : `Màu sắc/Size: ${Object.values(item.options).join(', ')}`}</p>
                            </td>
                            <td class="checkout-model">${p.sku}</td>
                            <td class="checkout-quantity">${item.quantity}</td>
                            <td class="checkout-price"><strong>${Checkout.formatPrice(item.price)}</strong></td>
                            <td class="checkout-total"><strong>${Checkout.formatPrice(item.line_total)}</strong></td>
                        </tr>`;
                });
                $tableBody.html(itemsHtml);

                // 3. Render bảng tổng kết tiền
                const s = cartData.summary;
                let summaryHtml = `
                    <li><em>Tạm tính</em> <strong class="price">${Checkout.formatPrice(s.subtotal)}</strong></li>
                    <li><em>Phí vận chuyển</em> <strong class="price">${Checkout.formatPrice(s.shipping_fee)}</strong></li>
                `;

                if (s.discount_amount > 0) {
                    summaryHtml += `<li><em>Giảm giá ${s.voucher_applied ? `(${s.voucher_applied.code})` : ''}</em> 
                                    <strong class="price text-danger">-${Checkout.formatPrice(s.discount_amount)}</strong></li>`;
                }

                summaryHtml += `<li class="checkout-total-price"><em>Tổng tiền</em> <strong class="price">${Checkout.formatPrice(s.final_total)}</strong></li>`;
                
                $summaryUl.html(summaryHtml);

                // Disable nút nếu có lỗi kho
                const hasError = cartData.items.some(i => i.is_error);
                $('#button-confirm').prop('disabled', hasError);

            } catch (e) {
                console.error("Lỗi tải thông tin xác nhận:", e);
                Swal.fire('Lỗi', 'Không thể tính toán phí vận chuyển hoặc tải đơn hàng.', 'error');
            }
        },

        placeOrder: async function () {
            const $btn = $('#button-confirm');
            
            // Validate lần cuối
            if (!Checkout.data.selectedAddressId || !Checkout.data.payment_method) {
                return Swal.fire('Lỗi', 'Vui lòng hoàn thành các bước trên.', 'error');
            }

            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang tạo đơn hàng...');

            try {
                const payload = {
                    address_id: Checkout.data.selectedAddressId,
                    payment_method: Checkout.data.payment_method,
                    note: $('#delivery-payment-method').val(), // Ghi chú từ Step 3
                };

                const response = await window.api.post('/api/v1/customer/orders', payload);

                if (response.data.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: 'Đơn hàng của bạn đã được tiếp nhận.',
                        confirmButtonText: 'Xem đơn hàng'
                    }).then(() => {
                        window.location.href = '/customer/orders/' + response.data.data.id;
                    });
                }
            } catch (err) {
                $btn.prop('disabled', false).text('Xác nhận đơn hàng');
                const errMsg = err.response?.data?.message || 'Giao dịch thất bại, vui lòng thử lại.';
                Swal.fire('Lỗi đặt hàng', errMsg, 'error');
            }
        }
    };
})();
// 1. Cập nhật hàm placeOrder trong module hiện tại của bạn
Checkout.OrderReview.placeOrder = async function () {
    const $btn = $('#button-confirm');
    
    if (!Checkout.data.selectedAddressId || !$('input[name="payment_method"]:checked').val()) {
        return Swal.fire('Lỗi', 'Vui lòng hoàn thành đầy đủ thông tin thanh toán.', 'error');
    }

    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang xử lý...');

    try {
        const payload = {
            address_id: Checkout.data.selectedAddressId,
            payment_method: $('input[name="payment_method"]:checked').val(),
            note: $('#delivery-payment-method').val(),
        };

        const response = await window.api.post('/api/v1/customer/orders', payload);

        if (response.data.status) {
    const orderId = response.data.data.id;

    Swal.fire({
        title: '🎉 Đặt hàng thành công!',
        text: "Cảm ơn bạn đã tin dùng dịch vụ của chúng tôi.",
        icon: 'success',
        showCancelButton: true,
        confirmButtonText: '<i class="fa fa-eye"></i> Xem đơn hàng',
        cancelButtonText: '<i class="fa fa-home"></i> Tiếp tục mua sắm',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            // KHẮC PHỤC TREO: Đợi một nhịp nhỏ (200-300ms) để SWAL dọn dẹp backdrop
            setTimeout(() => {
                OrderModule.showOrderDetail(orderId);
            }, 300);
        } else {
            window.location.href = '/';
        }
    });

    // QUAN TRỌNG: Reset nút bấm và dọn dẹp giỏ hàng ngay lập tức
    $btn.prop('disabled', false).text('XÁC NHẬN ĐẶT HÀNG');
    
    if (window.AppCart) window.AppCart.refresh();
}
    } catch (err) {
        $btn.prop('disabled', false).text('XÁC NHẬN ĐẶT HÀNG');
        Checkout.handleAjaxError(err);
    }
};

// 2. Tạo Module Order độc lập (Dùng chung cho cả Lịch sử đơn hàng)
var OrderModule = (function () {
    return {
        showOrderDetail: async function (orderId) {
            try {
                // Hiển thị loading nhẹ
                $('#order-modal-body').html('<div class="text-center"><i class="fa fa-refresh fa-spin fa-3x"></i><p>Đang tải chi tiết...</p></div>');
                $('#orderDetailModal').modal('show');

                const res = await window.api.get(`/api/v1/customer/orders/${orderId}`);
                if (res.data.status) {
                    this.renderOrderDetail(res.data.data);
                }
            } catch (e) {
                $('#orderDetailModal').modal('hide');
                Swal.fire('Lỗi', 'Không thể lấy thông tin đơn hàng.', 'error');
            }
        },

        renderOrderDetail: function (data) {
    const addr = data.shipping_address;
    $('#md-order-code').text(`[${data.code}]`);

    let itemsHtml = data.items.map(item => {
        // Xử lý hiển thị Options (Màu sắc, Size...)
        let optionsHtml = '';
        if (item.options && Object.keys(item.options).length > 0) {
            const labels = Object.entries(item.options).map(([key, val]) => `${val}`);
            optionsHtml = `<div class="text-muted" style="font-size: 11px;">
                            <i class="fa fa-tags"></i> ${labels.join(', ')}
                          </div>`;
        }

        return `
            <tr>
                <td class="text-center">
                    <img src="${item.thumbnail}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #eee;">
                </td>
                <td>
                    <div class="bold" style="color: #333;">${item.product_name}</div>
                    ${optionsHtml}
                </td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-right">${Checkout.formatPrice(item.price)}</td>
                <td class="text-right bold">${Checkout.formatPrice(item.total_line)}</td>
            </tr>
        `;
    }).join('');

    let html = `
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-6">
                <div class="well" style="background: #fff; border: 1px dashed #ccc; min-height: 130px;">
                    <h4 class="bold uppercase" style="color: #e84d1c; margin-top:0; font-size: 14px;">Địa chỉ nhận hàng</h4>
                    <p style="margin-bottom: 5px;"><strong>${addr.recipient_name}</strong></p>
                    <p style="margin-bottom: 5px;"><i class="fa fa-phone"></i> ${addr.phone}</p>
                    <p style="margin-bottom: 0; font-size: 12px; color: #666;"><i class="fa fa-map-marker"></i> ${addr.address_detail}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="well" style="background: #fff; border: 1px dashed #ccc; min-height: 130px;">
                    <h4 class="bold uppercase" style="color: #2e6da4; margin-top:0; font-size: 14px;">Trạng thái đơn hàng</h4>
                    <p>Trạng thái: <span class="label label-${data.status.color}">${data.status.label}</span></p>
                    <p>Thanh toán: <span class="badge badge-primary" style="background: #578ebe;">${data.payment_method}</span></p>
                    <p style="margin-bottom: 0;">Ngày đặt: <small>${data.created_at}</small></p>
                </div>
            </div>
        </div>

        <table class="table table-bordered table-hover">
            <thead>
                <tr style="background: #f5f5f5;">
                    <th class="text-center" width="10%">Ảnh</th>
                    <th>Sản phẩm</th>
                    <th class="text-center" width="10%">SL</th>
                    <th class="text-right" width="20%">Đơn giá</th>
                    <th class="text-right" width="20%">Thành tiền</th>
                </tr>
            </thead>
            <tbody>${itemsHtml}</tbody>
        </table>

        <div class="row">
            <div class="col-md-7">
                ${data.note ? `<div class="alert alert-warning" style="padding: 10px;"><b>Ghi chú:</b> ${data.note}</div>` : ''}
            </div>
            <div class="col-md-5 text-right">
                <div style="font-size: 13px; line-height: 2;">
                    <div>Tạm tính: <span class="bold">${Checkout.formatPrice(data.subtotal)}</span></div>
                    <div>Phí vận chuyển: <span class="bold">${Checkout.formatPrice(data.shipping_fee)}</span></div>
                    ${data.discount > 0 ? `<div>Giảm giá: <span class="bold text-danger">-${Checkout.formatPrice(data.discount)}</span></div>` : ''}
                    <hr style="margin: 10px 0;">
                    <div style="font-size: 18px; color: #e84d1c;">Tổng thanh toán: <span class="bold">${Checkout.formatPrice(data.total_amount)}</span></div>
                </div>
            </div>
        </div>
    `;
    $('#order-modal-body').html(html);
}
    };
})();