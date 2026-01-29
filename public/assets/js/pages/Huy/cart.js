class CartManager {
    constructor() {
        this.items = [];
        this.apiUrl = "/api/v1/customer/cart"; // Đường dẫn chuẩn trong api.php của Huy
    }

    init() {
        // Lắng nghe sự kiện mỗi khi có sản phẩm mới được thêm
        window.addEventListener("cart:updated", () => this.loadCart());
        this.loadCart();
    }

    async loadCart() {
        try {
            // Gọi GET /api/v1/customer/cart
            const response = await window.api.get(this.apiUrl);

            // Theo cấu trúc Laravel thông thường, dữ liệu nằm trong response.data
            // Huy kiểm tra nếu Controller trả về ['items' => ...] trực tiếp thì dùng response.data
            const cartData = response.data.data || response.data;

            this.items = cartData.items || [];
            this.renderMiniCart(cartData.total || 0);
            this.renderMainCartTable(cartData.total || 0);
        } catch (error) {
            console.error("Lỗi tải giỏ hàng:", error);
            if (error.response && error.response.status === 401) {
                console.warn(
                    "Huy ơi, bạn chưa đăng nhập nên API Cart không trả dữ liệu đâu!",
                );
            }
        }
    }

    // Hàm render bảng ở trang shopping-cart.blade.php
    renderMainCartTable(totalPrice) {
        const $tbody = $("#js-cart-body");
        if (!$tbody.length) return;

        if (this.items.length === 0) {
            $tbody.html(
                '<tr><td colspan="7" class="text-center">Giỏ hàng trống</td></tr>',
            );
            return;
        }

        let html = "";
        this.items.forEach((item) => {
            const price = item.product.sale_price || item.product.price || 0;
            const subtotal = price * item.quantity;
            // Lấy đúng đường dẫn ảnh từ API
            const imgUrl =
                item.product.thumb_url ||
                item.product.image_url ||
                "assets/pages/img/products/model7.jpg";

            html += `
        <tr>
            <td class="goods-page-image">
                <a href="javascript:;"><img src="${imgUrl}" alt="${item.product.name}"></a>
            </td>
            <td class="goods-page-description">
                <h3><a href="javascript:;">${item.product.name}</a></h3>
                <p><strong>Mã:</strong> ${item.product.sku || item.product.id}</p>
            </td>
            <td class="goods-page-ref-no">Ref: ${item.product.id}</td>
            <td class="goods-page-quantity">
                <strong>${item.quantity}</strong>
            </td>
            <td class="goods-page-price">
                <strong>${this.formatMoney(price)}</strong>
            </td>
            <td class="goods-page-total">
                <strong>${this.formatMoney(subtotal)}</strong>
            </td>
            
        </tr>`;
        });

        $tbody.html(html);
        $("#js-cart-subtotal, #js-cart-total-main").text(
            this.formatMoney(totalPrice),
        );
    }

    renderMiniCart() {
        const $container = $("#js-cart-items-list");
        const $count = $("#js-cart-count-top");
        const $total = $("#js-cart-total-top");

        let html = "";
        let total = 0;

        // API của Huy trả về data.data.items hoặc data.items
        const items = this.items;

        items.forEach((item) => {
            // Lấy đúng đường dẫn ảnh từ API (Huy check key thumb_url hoặc image_url)
            const imgUrl =
                item.product.thumb_url ||
                item.product.image_url ||
                "/assets/pages/img/products/model7.jpg";
            const price = item.product.sale_price || item.product.price || 0;
            total += price * item.quantity;

            html += `
            <li>
        <a href="javascript:void(0);"><img src="${imgUrl}" width="37" height="34" alt="${item.product.name}"></a>
        <span class="cart-content-count">x ${item.quantity}</span>
        <strong><a href="javascript:void(0);">${item.product.name}</a></strong>
        <em>${this.formatMoney(price)}</em>
        
        <a href="javascript:void(0);" class="del-goods" 
           onclick="window.cartApp.removeItem(${item.id})" 
           style="position: absolute; right: 10px; top: 15px; opacity: 1 !important; display: block !important; background: none !important;">
            
        </a>
    </li>`;
        });

        if ($container.length) {
            $container.html(
                html || '<li class="text-center">Giỏ hàng trống</li>',
            );
        }

        if ($count.length) $count.text(`${items.length} SP`);
        if ($total.length) $total.text(this.formatMoney(total));
    }

    async removeItem(itemId) {
        if (!confirm("Xóa sản phẩm này khỏi giỏ?")) return;
        try {
            // Gọi DELETE /api/v1/customer/cart/{id} đúng như api.php
            await window.api.delete(`${this.apiUrl}/${itemId}`);
            this.loadCart();
        } catch (e) {
            alert("Không thể xóa. Có thể do chưa đăng nhập hoặc lỗi Server.");
        }
    }

    formatMoney(amount) {
        return new Intl.NumberFormat("vi-VN").format(amount) + " ₫";
    }
}

// Khởi tạo
$(document).ready(() => {
    window.cartApp = new CartManager();
    window.cartApp.init();
});
