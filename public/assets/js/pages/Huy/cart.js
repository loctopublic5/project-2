class CartManager {
    constructor() {
        this.apiBase = "/api/v1/customer/cart";
        // Selector cho trang Shopping Cart
        this.cartTableBody = $("#cart-items-list"); // Huy thêm id này vào tbody trong file cart.blade.php
        this.subTotalElem = $("#sub-total");
        this.finalTotalElem = $("#final-total");

        // Selector cho Mini Cart (Header)
        this.miniCartCount = $(".top-cart-info-count"); // Chỗ hiển thị "3 items"
        this.miniCartTotal = $(".top-cart-info-value"); // Chỗ hiển thị "$100.00"
    }

    async init() {
        console.log("Huy ơi, Cart Manager đã sẵn sàng!");
        this.initEventListeners();
        // Chỉ nạp dữ liệu nếu đang ở trang giỏ hàng
        if (this.cartTableBody.length > 0) {
            await this.loadCartDetail();
        }
    }

    // 1. LẤY CHI TIẾT GIỎ HÀNG (Dùng Route: GET /api/v1/customer/cart)
    async loadCartDetail() {
        try {
            const response = await axios.get(this.apiBase);
            if (response.data.status === "success") {
                this.renderFullCart(response.data.data);
            }
        } catch (error) {
            console.error("Lỗi load giỏ hàng:", error);
        }
    }

    // 2. THÊM VÀO GIỎ HÀNG (Dùng Route: POST /api/v1/customer/cart)
    async addToCart(productId, quantity = 1, options = {}) {
        try {
            const response = await axios.post(this.apiBase, {
                product_id: productId,
                quantity: quantity,
                options: options,
            });

            if (response.data.status === "success") {
                alert("Thêm vào giỏ hàng thành công!");
                // Sau khi thêm xong, cần cập nhật lại số lượng ở Header
                await this.updateHeaderCart();
            }
        } catch (error) {
            const msg =
                error.response?.data?.message || "Không thêm được hàng Huy ơi!";
            alert(msg);
        }
    }

    // 3. XÓA 1 ITEM (Dùng Route: DELETE /api/v1/customer/cart/{id})
    async removeItem(itemId) {
        if (!confirm("Huy có chắc muốn bỏ món này không?")) return;

        try {
            const response = await axios.delete(`${this.apiBase}/${itemId}`);
            if (response.data.status === "success") {
                // Xóa xong thì nạp lại toàn bộ trang giỏ hàng cho đồng bộ
                await this.loadCartDetail();
                await this.updateHeaderCart();
            }
        } catch (error) {
            alert("Lỗi khi xóa món hàng!");
        }
    }

    // 4. RENDER GIAO DIỆN TRANG GIỎ HÀNG
    renderFullCart(cartData) {
        let html = "";
        const items = cartData.items || [];

        if (items.length === 0) {
            html =
                '<tr><td colspan="7" class="text-center">Giỏ hàng của Huy đang trống!</td></tr>';
        } else {
            items.forEach((item) => {
                // Lưu ý: item.id ở đây là ID của dòng trong cart_items, không phải product_id
                html += `
                <tr>
                    <td class="goods-page-image">
                        <a href="javascript:;"><img src="${item.product_image}" alt="${item.product_name}"></a>
                    </td>
                    <td class="goods-page-description">
                        <h3><a href="javascript:;">${item.product_name}</a></h3>
                        <p><strong>SKU:</strong> ${item.sku || "N/A"}</p>
                    </td>
                    <td class="goods-page-ref-no">${item.sku || ""}</td>
                    <td class="goods-page-quantity">
                        <div class="product-quantity">
                            <input type="text" value="${item.quantity}" readonly class="form-control input-sm">
                        </div>
                    </td>
                    <td class="goods-page-price"><strong><span>$</span>${item.price}</strong></td>
                    <td class="goods-page-total"><strong><span>$</span>${item.subtotal}</strong></td>
                    <td class="del-goods-col">
                        <a class="del-goods" href="javascript:;" onclick="Cart.removeItem(${item.id})">&nbsp;</a>
                    </td>
                </tr>`;
            });
        }

        this.cartTableBody.html(html);
        this.subTotalElem.text(`$${cartData.sub_total || 0}`);
        this.finalTotalElem.text(
            `$${cartData.total_amount || cartData.sub_total || 0}`,
        );
    }

    // 5. CẬP NHẬT HEADER (Mini Cart)
    async updateHeaderCart() {
        try {
            const response = await axios.get(this.apiBase);
            if (response.data.status === "success") {
                const cart = response.data.data;
                const count = cart.items ? cart.items.length : 0;
                this.miniCartCount.text(`${count} items`);
                this.miniCartTotal.text(`$${cart.sub_total || 0}`);
            }
        } catch (e) {
            console.log("Lỗi update header");
        }
    }

    initEventListeners() {
        // Bắt sự kiện cho nút "Add to Cart" ở trang chủ/danh sách
        $(document).on("click", ".add2cart, .btn-addcart", (e) => {
            e.preventDefault();
            const btn = $(e.currentTarget);
            const productId = btn.data("id");
            if (productId) {
                this.addToCart(productId, 1);
            }
        });
    }
}

// Khởi tạo toàn cục để dùng được onclick="Cart.removeItem()"
window.Cart = new CartManager();
$(document).ready(() => window.Cart.init());
