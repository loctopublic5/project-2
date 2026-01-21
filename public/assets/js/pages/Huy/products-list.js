// public/assets/js/pages/Huy/products-list.js

class ProductListUI {
    constructor() {
        this.currentPage = 1;
        this.limit = 24;
        this.filters = {
            minPrice: 0,
            maxPrice: 10000000,
            inStock: false,
            notAvailable: false,
        };
        this.sort = {
            field: "created_at",
            order: "desc",
        };

        this.init();
    }

    init() {
        this.initPriceSlider();
        this.initEventListeners();
        this.loadProducts();
    }

    initPriceSlider() {
        const self = this;
        $("#slider-range").slider({
            range: true,
            min: 0,
            max: 10000000,
            values: [0, 10000000],
            slide: function (event, ui) {
                $("#amount").val(
                    self.formatPrice(ui.values[0]) +
                        " - " +
                        self.formatPrice(ui.values[1]),
                );
            },
            stop: function (event, ui) {
                self.filters.minPrice = ui.values[0];
                self.filters.maxPrice = ui.values[1];
                self.loadProducts();
            },
        });
        $("#amount").val(
            this.formatPrice(0) + " - " + this.formatPrice(10000000),
        );
    }

    initEventListeners() {
        const self = this;

        // Sắp xếp
        $(".list-view-sorting select")
            .eq(1)
            .on("change", function () {
                const val = $(this).val();
                if (
                    val.includes("pd.name&order=ASC") ||
                    val.includes("pd.name&order=DESC")
                ) {
                    self.sort = { field: "latest", order: "desc" };
                } else if (val.includes("p.price&order=ASC")) {
                    self.sort = { field: "price", order: "asc" };
                } else if (val.includes("p.price&order=DESC")) {
                    self.sort = { field: "price", order: "desc" };
                } else if (val.includes("rating")) {
                    self.sort = { field: "latest", order: "desc" };
                } else {
                    self.sort = { field: "latest", order: "desc" };
                }
                self.loadProducts();
            });

        // Giới hạn hiển thị
        $(".list-view-sorting select")
            .eq(0)
            .on("change", function () {
                self.limit = parseInt($(this).val().split("=")[1]);
                self.loadProducts();
            });

        // Chuyển đổi view
        $(".list-view a").on("click", function (e) {
            e.preventDefault();
            $(".list-view a").removeClass("active");
            $(this).addClass("active");

            if ($(this).find(".fa-th-large").length > 0) {
                $("#real-product-container").removeClass("list-view-mode");
            } else {
                $("#real-product-container").addClass("list-view-mode");
            }
        });
    }

    async loadProducts() {
        try {
            const params = {
                page: this.currentPage,
                limit: this.limit,
            };

            // Filter giá
            if (this.filters.minPrice > 0) {
                params.min_price = this.filters.minPrice;
            }
            if (this.filters.maxPrice < 10000000) {
                params.max_price = this.filters.maxPrice;
            }

            // Sorting - Chuyển đổi sang format backend
            if (this.sort.field === "price") {
                params.sort_by =
                    this.sort.order === "asc" ? "price_asc" : "price_desc";
            } else {
                params.sort_by = "latest";
            }

            console.log("Sending params:", params); // Debug

            const response = await axios.get("/api/v1/products", { params });

            if (response.data.status) {
                this.renderProducts(response.data.data);
            }
        } catch (error) {
            console.error("Error:", error);
            Swal.fire({
                icon: "error",
                title: "Lỗi!",
                text:
                    error.response?.data?.message || "Không thể tải sản phẩm!",
            });
        }
    }

    renderProducts(products) {
        const container = $("#real-product-container");
        container.empty();

        if (!products || products.length === 0) {
            container.html(
                '<div class="col-md-12"><p class="text-center">Không có sản phẩm nào.</p></div>',
            );
            return;
        }

        products.forEach((product) => {
            const html = this.createProductHTML(product);
            container.append(html);
        });

        this.initProductEvents();
    }
    createProductHTML(product) {
        const { pricing, info, inventory } = product;

        // 1. Kiểm tra điều kiện giảm giá
        const hasSale =
            pricing.sale_price > 0 &&
            pricing.sale_price < pricing.original_price;
        const displayPrice = hasSale
            ? pricing.sale_price
            : pricing.original_price;

        // 2. Ảnh thumbnail
        const thumbnail =
            info.thumbnail || "assets/pages/img/products/model1.jpg";

        return `
        <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="product-item">
                <div class="pi-img-wrapper">
                    <img src="${thumbnail}" class="img-responsive" alt="${info.name}">
                    <div>
                        <a href="${thumbnail}" class="btn btn-default fancybox-button">Zoom</a>
                        <a href="javascript:void(0);" class="btn btn-default quick-view" data-id="${product.id}">View</a>
                    </div>
                </div>
                <h3><a href="/products/${info.slug}">${info.name}</a></h3>
                <div class="pi-price">
                    ${this.formatPrice(displayPrice)}
                    ${hasSale ? `<span style="text-decoration: line-through; color: #bbb; margin-left: 8px; font-weight: normal;">${this.formatPrice(pricing.original_price)}</span>` : ""}
                </div>
                
                <a href="javascript:void(0);" class="btn btn-default add2cart" data-id="${product.id}" ${!inventory.in_stock ? "disabled" : ""}>
                    <i class="fa fa-shopping-cart"></i> Add to cart
                </a>

                ${hasSale ? `<div class="sticker sticker-sale"></div>` : ""}
                
                ${!inventory.in_stock ? '<div class="sticker sticker-out-of-stock" style="background: #999; color: #fff;">HẾT</div>' : ""}
            </div>
        </div>
    `;
    }
    formatPrice(price) {
        return new Intl.NumberFormat("vi-VN").format(price) + " ₫";
    }

    initProductEvents() {
        const self = this;

        $(".add2cart")
            .off("click")
            .on("click", function () {
                const productId = $(this).data("id");
                self.addToCart(productId);
            });

        $(".quick-view")
            .off("click")
            .on("click", function () {
                const productId = $(this).data("id");
                self.showQuickView(productId);
            });

        // Fancybox
        if (typeof $.fancybox !== "undefined") {
            $(".fancybox-button").fancybox();
        }
    }

    async addToCart(productId) {
        try {
            await axios.post("/api/v1/customer/cart", {
                product_id: productId,
                quantity: 1,
            });

            Swal.fire({
                icon: "success",
                title: "Thành công!",
                text: "Đã thêm vào giỏ hàng",
                timer: 1500,
                showConfirmButton: false,
            });

            this.updateCartCount();
        } catch (error) {
            if (error.response?.status === 401) {
                Swal.fire({
                    icon: "warning",
                    title: "Chưa đăng nhập!",
                    text: "Vui lòng đăng nhập để thêm vào giỏ hàng",
                    confirmButtonText: "Đăng nhập",
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "/login";
                    }
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Lỗi!",
                    text:
                        error.response?.data?.message ||
                        "Không thể thêm vào giỏ hàng",
                });
            }
        }
    }

    async showQuickView(productId) {
        try {
            // 1. Gọi API lấy dữ liệu chi tiết
            const response = await axios.get(`/api/v1/products/${productId}`);
            const product = response.data.data;
            const { info, pricing, inventory } = product;

            const $modal = $("#product-pop-up");
            const thumbnail =
                info.thumbnail || "assets/pages/img/products/model7.jpg";
            const hasSale =
                pricing.sale_price > 0 &&
                pricing.sale_price < pricing.original_price;
            const displayPrice = hasSale
                ? pricing.sale_price
                : pricing.original_price;

            // 2. Đổ dữ liệu vào đúng các thẻ bạn vừa gửi
            $modal.find(".product-main-image img").attr("src", thumbnail);
            $modal.find("h1").text(info.name);
            $modal
                .find(".description p")
                .text(info.description || "Chưa có mô tả cho sản phẩm này.");

            // Xử lý giá
            $modal.find(".price strong").html(this.formatPrice(displayPrice));
            if (hasSale) {
                $modal
                    .find(".price em")
                    .html(this.formatPrice(pricing.original_price))
                    .show();
                $modal.find(".sticker-sale").show(); // Hiện chữ SALE đỏ
            } else {
                $modal.find(".price em").hide();
                $modal.find(".sticker-sale").hide();
            }

            // Tình trạng kho
            $modal
                .find(".availability strong")
                .text(inventory.in_stock ? "In Stock" : "Out of Stock");

            // Gán link cho nút More details
            $modal
                .find(".btn-default[href]")
                .attr("href", `/products/${info.slug}`);

            // 3. Mở Fancybox bằng đối tượng jQuery
            if (typeof $.fancybox !== "undefined") {
                $.fancybox.open($modal, {
                    type: "inline",
                    autoSize: false,
                    width: 700,
                    afterShow: function () {
                        // Khởi tạo lại Touchspin cho ô số lượng nếu cần
                        if (typeof Layout !== "undefined") {
                            Layout.initTouchspin();
                        }
                    },
                });
            }
        } catch (error) {
            console.error("Lỗi Quick View:", error);
            Swal.fire({
                icon: "error",
                title: "Lỗi!",
                text: "Không thể tải thông tin sản phẩm này.",
            });
        }
    }
    async updateCartCount() {
        try {
            const response = await axios.get("/api/v1/customer/cart");
            if (response.data.status) {
                const items = response.data.data;
                const count = items.length;
                const total = items.reduce(
                    (sum, item) => sum + item.price * item.quantity,
                    0,
                );

                $(".top-cart-info-count").text(`${count} items`);
                $(".top-cart-info-value").text(this.formatPrice(total));
            }
        } catch (error) {
            console.error("Error:", error);
        }
    }
}

// Khởi tạo
jQuery(document).ready(function () {
    new ProductListUI();
});
