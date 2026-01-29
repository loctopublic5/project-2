const Cart = {
    init: function() {
        this.loadMiniCart();
        // Lắng nghe sự kiện để cập nhật lại khi các module khác thay đổi giỏ hàng
        window.addEventListener('cart:updated', () => this.loadMiniCart());
    },

    loadMiniCart: function() {
        window.api.get('/api/v1/customer/cart')
            .then(res => {
                // Laravel Resource bọc dữ liệu trong data.data
                const cartData = res.data.data; 
                this.renderMiniCart(cartData);
            })
            .catch(err => {
                console.error("Lỗi tải giỏ hàng:", err);
                this.renderEmptyState();
            });
    },

    renderMiniCart: function(data) {
        // data ở đây là res.data.data từ CartResource
        const items = data.items || [];
        const summary = data.summary || {};

        // 1. Cập nhật Topbar (Số lượng & Tiền rút gọn)
        const totalQty = items.reduce((sum, item) => sum + item.quantity, 0);
        $('#js-cart-count-top').text(`${totalQty} SP`);
        $('#js-cart-total-top').text(this.formatCurrency(summary.final_total, true));

        // 2. Render danh sách Dropdown
        let html = '';
        if (items.length > 0) {
            items.forEach(item => {
                // Mapping đúng theo CartItemResource thực tế của bạn
                const info = item.product_info || {}; 
                const price = item.price || 0;
                const quantity = item.quantity || 0;
                
                // Xử lý ảnh: Resource trả về 'avatar' bên trong 'product_info'
                const thumb = info.avatar || '/assets/pages/img/no-image.png';
                
                // --- PHẦN CHỈNH SỬA: XỬ LÝ CSS ERROR ---
                const isError = !!item.is_error;
                const itemClass = isError ? 'cart-item-error' : '';
                const errorHint = isError ? `<span class="cart-error-text"><i class="fa fa-warning"></i> ${item.error_message}</span>` : '';

                html += `
                    <li class="${itemClass}">
                        <a href="javascript:void(0);">
                            <img src="${thumb}" alt="${info.name}" width="37" height="34">
                        </a>
                    <span class="cart-content-count">x ${quantity}</span>
                    <strong>
                        <a href="javascript:void(0);">${info.name || 'Sản phẩm không xác định'}</a>
                            ${errorHint}
                    </strong>
                    <em>${this.formatCurrency(price)}</em>
                </li>`;
            });
        } else {
            html = '<li class="text-center" style="padding: 20px;">Giỏ hàng của bạn đang trống</li>';
        }

        $('#js-cart-items-list').html(html);
        
        // Khởi tạo lại SlimScroll cho Metronic
        if ($.fn.slimScroll) {
            const $list = $('#js-cart-items-list');
            if ($list.parent('.slimScrollDiv').length > 0) {
                $list.parent().replaceWith($list);
            }
            $list.slimScroll({ height: '250px', allowPageScroll: true });
        }
    },

    /**
     * Hàm format tiền tệ VND
     */
    formatCurrency: function(amount, isShort = false) {
        if (!amount || amount <= 0) return '0đ';

        if (isShort) {
            if (amount >= 1000000000) {
                return (amount / 1000000000).toFixed(1).replace(/\.0$/, '') + ' Tỷ';
            }
            if (amount >= 1000000) {
                return (amount / 1000000).toFixed(1).replace(/\.0$/, '') + ' Tr';
            }
            if (amount >= 1000) {
                return (amount / 1000).toFixed(0) + 'K';
            }
        }

        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND',
        }).format(amount).replace('₫', 'đ');
    },

    renderEmptyState: function() {
        $('#js-cart-count-top').text('0 SP');
        $('#js-cart-total-top').text('0đ');
        $('#js-cart-items-list').html('<li class="text-center" style="padding: 20px;">Giỏ hàng trống</li>');
    }
};

$(document).ready(() => Cart.init());