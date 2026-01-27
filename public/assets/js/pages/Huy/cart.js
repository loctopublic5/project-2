class CartManager {
    constructor() {
        // Selector cho trang giỏ hàng chính và mini-cart trên header
        this.cartTableBody = $("#cart-items-list"); // Huy nhớ thêm ID này vào tbody ở file Blade nhé
        this.totalPriceElem = $(".shopping-total-price .price");
        this.miniCartContainer = $(".top-cart-content-wrapper");

        this.apiEndpoint = "/api/cart"; // Đường dẫn API giỏ hàng của Huy
    }

    async init() {
        console.log("Huy ơi, Cart Manager đang khởi động...");
        await this.loadCartData();
        this.initEventListeners();
    }

    // 1. Lấy dữ liệu giỏ hàng từ Server (Async/Await theo ý Lộc)
    async loadCartData() {
        try {
            // Hiện cái spinner Shopee Huy vừa làm ở đây
            $("#shopee-loader").css("display", "flex");

            const response = await axios.get(this.apiEndpoint);

            if (response.data.success) {
                this.renderFullCart(response.data.cart);
                this.renderMiniCart(response.data.cart);
            }
        } catch (error) {
            console.error("Lỗi khi nạp giỏ hàng:", error);
        } finally {
            $("#shopee-loader").hide();
        }
    }

    // 2. Đổ dữ liệu vào trang Giỏ hàng chính (Trang Huy vừa tách Blade)
    renderFullCart(cart) {
        if (!this.cartTableBody.length) return; // Nếu không phải trang giỏ hàng thì bỏ qua

        let html = "";
        if (cart.items.length === 0) {
            html =
                '<tr><td colspan="6" class="text-center">Giỏ hàng trống!</td></tr>';
        } else {
            cart.items.forEach((item) => {
                html += `
                <tr>
                    <td class="goods-page-image">
                        <a href="/product/${item.slug}"><img src="${item.image}" alt="${item.name}"></a>
                    </td>
                    <td class="goods-page-description">
                        <h3><a href="/product/${item.slug}">${item.name}</a></h3>
                        <p><strong>Màu:</strong> ${item.color} - <strong>Size:</strong> ${item.size}</p>
                    </td>
                    <td class="goods-page-ref-no">${item.sku}</td>
                    <td class="goods-page-quantity">
                        <div class="product-quantity">
                            <input type="text" value="${item.quantity}" readonly class="form-control input-sm">
                        </div>
                    </td>
                    <td class="goods-page-price"><strong><span>$</span>${item.price}</strong></td>
                    <td class="goods-page-total"><strong><span>$</span>${(item.price * item.quantity).toFixed(2)}</strong></td>
                    <td class="del-goods-col">
                        <a class="del-goods" href="javascript:;" onclick="Cart.removeItem(${item.id})">&nbsp;</a>
                    </td>
                </tr>`;
            });
        }
        this.cartTableBody.html(html);
        // Cập nhật tổng tiền
        $("#sub-total").text(`$${cart.subtotal}`);
        $("#final-total").text(`$${cart.total}`);
    }

    // 3. Hàm xóa sản phẩm (Xử lý bất đồng bộ)
    async removeItem(itemId) {
        if (confirm("Huy có chắc muốn bỏ sản phẩm này không?")) {
            try {
                const response = await axios.delete(
                    `${this.apiEndpoint}/remove/${itemId}`,
                );
                if (response.data.success) {
                    await this.loadCartData(); // Nạp lại dữ liệu sau khi xóa
                }
            } catch (error) {
                alert("Không xóa được sản phẩm rồi Huy ơi!");
            }
        }
    }
}

// Khởi tạo đối tượng toàn cục
window.Cart = new CartManager();
$(document).ready(() => window.Cart.init());
