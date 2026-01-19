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