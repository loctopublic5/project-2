import "./bootstrap";
import "../css/app.css";
import axiosClient from "./api/axiosClient";

const initApp = async () => {
    const authSection = document.getElementById("auth-section");
    const categoryMenu = document.getElementById("category-menu");
    const productContainer = document.getElementById("product-list");
    const token = localStorage.getItem("access_token");

    updateCartHeader();

    // ===== AUTH & WALLET =====
    if (token) {
        try {
            const wallet = await axiosClient.get("/customer/wallet");
            renderUserUI(authSection, wallet);
            setupLogoutHandler();
        } catch {
            renderGuestUI(authSection);
        }
    } else {
        renderGuestUI(authSection);
    }

    // ===== CATEGORIES =====
    try {
        const res = await axiosClient.get("/categories");
        const categories = res.data ?? [];

        if (categoryMenu) {
            categoryMenu.innerHTML =
                `<li><a href="/">Trang chủ</a></li>
                 <li><a href="/products">Sản phẩm</a></li>` +
                categories
                    .map(
                        (cat) =>
                            `<li><a href="/category/${cat.slug}">${cat.name}</a></li>`
                    )
                    .join("");
        }
    } catch (e) {
        console.error("CATEGORY ERROR", e);
    }

    // ===== PRODUCTS =====
    if (productContainer) {
        await renderProducts(productContainer);
    }
};

// ================= PRODUCTS =================
const renderProducts = async (container) => {
    try {
        const res = await axiosClient.get("/products");
        console.log("API PRODUCTS:", res);

        const products = res.data ?? [];

        if (products.length === 0) {
            container.innerHTML = `
                <div class="col-md-12 text-center py-5">
                    <h3>Chưa có sản phẩm nào</h3>
                </div>`;
            return;
        }

        container.innerHTML = products
            .map((p) => {
                const name = p.info?.name ?? "Sản phẩm";
                const image =
                    p.info?.thumbnail ??
                    "https://placehold.co/400x400?text=No+Image";
                const price = p.pricing?.is_sale_active
                    ? p.pricing.sale_price
                    : p.pricing?.original_price ?? 0;

                return `
                <div class="col-md-3 col-sm-4 col-xs-6">
                    <div class="product-item">
                        <div class="pi-img-wrapper">
                            <img src="${image}"
                                 class="img-responsive"
                                 style="height:230px;width:100%;object-fit:cover;">
                            <div>
                                <a href="/product/${
                                    p.id
                                }" class="btn btn-default">
                                    Chi tiết
                                </a>
                            </div>
                        </div>
                        <h3>${name}</h3>
                        <div class="pi-price">
                            ${Number(price).toLocaleString("vi-VN")} đ
                        </div>
                        <a href="javascript:void(0)"
                           class="btn btn-default add2cart btn-add-cart"
                           data-id="${p.id}"
                           data-name="${name}"
                           data-price="${price}">
                           Thêm vào giỏ
                        </a>
                    </div>
                </div>`;
            })
            .join("");

        setupCartHandler();
    } catch (error) {
        console.error("PRODUCT ERROR", error);
        container.innerHTML = `
            <div class="col-md-12 text-center text-danger">
                Lỗi kết nối máy chủ
            </div>`;
    }
};

// ================= CART =================
const updateCartHeader = () => {
    const cartCount = document.getElementById("cart-count");
    const cartTotal = document.getElementById("cart-total");
    const cart = JSON.parse(localStorage.getItem("cart")) || [];

    const totalItems = cart.reduce((s, i) => s + i.qty, 0);
    const totalPrice = cart.reduce((s, i) => s + i.qty * i.price, 0);

    if (cartCount) cartCount.innerText = `${totalItems} items`;
    if (cartTotal)
        cartTotal.innerText = `${totalPrice.toLocaleString("vi-VN")} đ`;
};

const setupCartHandler = () => {
    document.querySelectorAll(".btn-add-cart").forEach((btn) => {
        btn.onclick = () => {
            const product = {
                id: btn.dataset.id,
                name: btn.dataset.name,
                price: Number(btn.dataset.price),
                qty: 1,
            };

            let cart = JSON.parse(localStorage.getItem("cart")) || [];
            const index = cart.findIndex((i) => i.id === product.id);

            index !== -1 ? cart[index].qty++ : cart.push(product);

            localStorage.setItem("cart", JSON.stringify(cart));
            updateCartHeader();
            alert(`Đã thêm ${product.name} vào giỏ`);
        };
    });
};

// ================= AUTH UI =================
const renderGuestUI = (c) => {
    if (c)
        c.innerHTML = `
            <li><a href="/login">Đăng nhập</a></li>
            <li><a href="/register">Đăng ký</a></li>`;
};

const renderUserUI = (c, d) => {
    if (c) {
        const name = d?.user?.full_name ?? "Thành viên";
        const balance = d?.balance ?? 0;
        c.innerHTML = `
            <li><a href="/profile">${name}</a></li>
            <li><a href="#">Ví: ${balance.toLocaleString()} đ</a></li>
            <li><a href="javascript:void(0)" id="btnLogout">Thoát</a></li>`;
    }
};

const setupLogoutHandler = () => {
    const b = document.getElementById("btnLogout");
    if (b)
        b.onclick = () => {
            localStorage.removeItem("access_token");
            location.reload();
        };
};

document.addEventListener("DOMContentLoaded", initApp);
