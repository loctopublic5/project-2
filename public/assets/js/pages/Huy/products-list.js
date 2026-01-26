// public/assets/js/pages/Huy/products-list.js

class ProductList {
    constructor() {
        this.currentPage = 1;
        this.limit = 9;
        this.filters = {
            minPrice: 0,
            maxPrice: 10000000,
            inStock: false,
            notAvailable: false,
            category_id: "",
        };
        this.sort = {
            field: "created_at",
            order: "desc",
        };
    }

    async init() {
        try {
            // 1. Khởi tạo slider giá trước
            this.initPriceSlider();

            // 2. Phải đợi nạp xong danh mục để có cái mà click
            await this.loadCategories();

            // 3. Nạp sản phẩm và hàng bán chạy song song cho nhanh
            await Promise.all([this.loadProducts(), this.loadBestsellers()]);

            // 4. CUỐI CÙNG mới gán sự kiện. Lúc này các thẻ .category-link đã hiện hồn trên web
            this.initEventListeners();

            console.log("Huy ơi, tất cả đã sẵn sàng!");
        } catch (error) {
            console.error("Lỗi nạp dữ liệu từ API:", error);
        }
    }

    async loadCategories() {
        try {
            const response = await axios.get("/api/v1/categories"); // Dùng GET như đồng nghiệp gợi ý
            if (response.data && response.data.data) {
                this.renderCategories(response.data.data);
            }
        } catch (error) {
            console.error("Lỗi nạp danh mục:", error);
        }
    }

    renderCategories(categories) {
        const container = $("#sidebar-categories"); // Dùng ID cho chắc Huy nhé
        let html = "";

        categories.forEach((cat) => {
            if (!cat.parent_id) {
                html += `
            <li class="list-group-item clearfix dropdown">
                <a href="javascript:void(0);" class="category-link" data-id="${cat.id}">
                    <i class="fa fa-angle-right"></i> ${cat.name}
                </a>
                ${this.renderSubCategories(categories, cat.id)}
            </li>`;
            }
        });

        container.html(html);

        // QUAN TRỌNG: Gọi lại Layout của Metronic để nó nhận diện các menu mới vừa vẽ
        if (typeof Layout !== "undefined") {
            Layout.initTwitter(); // Hoặc Layout.init() tùy phiên bản Metronic bạn dùng
        }
    }

    renderSubCategories(allCategories, parentId) {
        const subs = allCategories.filter((c) => c.parent_id === parentId);
        if (subs.length === 0) return "";

        let subHtml = '<ul class="dropdown-menu" style="display:block;">';
        subs.forEach((sub) => {
            subHtml += `
            <li class="list-group-item dropdown clearfix">
                <a href="javascript:void(0);" class="category-link" data-id="${sub.id}">
                    <i class="fa fa-angle-right"></i> ${sub.name}
                </a>
                ${this.renderSubCategories(allCategories, sub.id)} </li>`;
        });
        subHtml += "</ul>";
        return subHtml;
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

        $(document).on("click", ".category-link", (e) => {
            e.preventDefault();
            const catId = $(e.currentTarget).data("id");

            console.log("Đã click và nhận ID:", catId); // Dòng này của Huy đã chạy ok

            // BƯỚC QUAN TRỌNG: Cập nhật filter để hàm loadProducts có thể lấy dữ liệu
            this.filters.category_id = catId;

            // Reset về trang 1
            this.currentPage = 1;

            // Gọi hàm tải sản phẩm
            this.loadProducts();
        });
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

    async loadProducts(page = 1) {
        try {
            this.currentPage = page;

            // BƯỚC QUAN TRỌNG: Kiểm tra xem category_id có giá trị không
            const catIdValue = this.filters.category_id || "";
            const params = {
                page: this.currentPage,
                limit: this.limit,
                // Đảm bảo lấy đúng từ this.filters mà bạn đã gán khi click
                category_id: catIdValue,
                min_price: this.filters.minPrice,
                max_price: this.filters.maxPrice,
                // Chỉnh lại sort_by cho khớp với switch-case trong ProductService.php
                sort_by:
                    this.sort.field === "price"
                        ? `price_${this.sort.order}`
                        : "latest",
            };

            console.log("Huy gửi params này lên Server:", params);

            // Dùng window.api (Axios có Token)
            const response = await window.api.get("/api/v1/products", {
                params,
            });

            // Tìm đoạn này trong loadProducts và sửa lại:
            if (response.data.status === true) {
                // 1. Lấy trực tiếp mảng sản phẩm từ response.data.data
                const products = response.data.data;

                // 2. Log ra để Huy thấy mảng này đã "về bản" chưa
                console.log("Danh sách sản phẩm nhận được:", products);

                // 3. Vẽ sản phẩm ra màn hình
                this.renderProducts(products);

                // 4. Tạm thời ẩn cái này đi nếu Backend của bạn chưa có phân trang meta
                // this.renderPagination(response.data);
            }
        } catch (error) {
            console.error("Lỗi tải sản phẩm:", error);
        }
    }
    // Hàm này gọi song song với loadProducts chính
    async loadBestsellers() {
        try {
            // Gọi API list nhưng giới hạn chỉ lấy 3-4 cái mới nhất/đắt nhất tùy ý
            const params = {
                limit: 4,
                sort_by: "latest", // Hoặc 'price_desc' nếu muốn hiện hàng cao cấp
            };

            const response = await axios.get("/api/v1/products", { params });

            if (response.data.status) {
                this.renderBestsellers(response.data.data);
            }
        } catch (error) {
            console.error("Lỗi lấy Bestsellers:", error);
        }
    }

    renderBestsellers(products) {
        // Tìm đến đúng cái div chứa danh sách Bestseller ở Sidebar
        const container = $(".sidebar-products");
        container.empty();

        products.forEach((product) => {
            // format giá tiền Việt Nam
            const price =
                new Intl.NumberFormat("vi-VN").format(
                    product.pricing.sale_price,
                ) + " ₫";
            const name = product.info.name;
            const thumb = product.info.thumbnail;
            const id = product.id;

            const html = `
            <div class="item">
                <a href="/product-detail/${id}">
                    <img src="${thumb}" alt="${name}" class="img-responsive">
                </a>
                <h3><a href="/product-detail/${id}">${name}</a></h3>
                <div class="price">${price}</div>
            </div>`;
            container.append(html);
        });
    }
    // --- RENDER PHÂN TRANG ---
    renderPagination(meta) {
        if (!meta || typeof meta !== "object") {
            console.error("Dữ liệu meta không hợp lệ:", meta);
            return;
        }

        const currentPage = meta.current_page || 1;
        const totalPages = meta.total_pages || 1; // Biến có s
        const totalItems = meta.total_items || 0;

        $("#pagination-info").html(`Total ${totalItems} items`);

        let html = "";
        // Nút Back
        html += `<li class="${currentPage === 1 ? "disabled" : ""}">
                <a href="javascript:;" data-page="${currentPage - 1}">«</a>
             </li>`;

        // SỬA TẠI ĐÂY: Phải là totalPages
        for (let i = 1; i <= totalPages; i++) {
            html += `<li class="${i === currentPage ? "active" : ""}">
                    <a href="javascript:;" data-page="${i}">${i}</a>
                 </li>`;
        }

        // SỬA TẠI ĐÂY: Phải là totalPages
        html += `<li class="${currentPage === totalPages ? "disabled" : ""}">
                <a href="javascript:;" data-page="${currentPage + 1}">»</a>
             </li>`;

        $("#product-pagination").html(html);

        const self = this;
        $("#product-pagination a")
            .off("click")
            .on("click", function (e) {
                e.preventDefault();
                const page = $(this).data("page");
                // SỬA TẠI ĐÂY: Phải là totalPages
                if (page > 0 && page <= totalPages && page !== currentPage) {
                    self.loadProducts(page);
                }
            });
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
        </div>`;
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
            const response = await axios.get(`/api/v1/products/${productId}`);
            const product = response.data.data;
            // Lấy thêm 'info.images' từ API
            const { info, pricing, inventory } = product;

            const $modal = $("#product-pop-up");
            const thumbnail =
                info.thumbnail || "assets/pages/img/products/model7.jpg";

            // --- PHẦN MỚI: XỬ LÝ GALLERY (ẢNH NHỎ) ---
            const $galleryContainer = $modal.find(".product-other-images");
            $galleryContainer.empty(); // Xóa ảnh cũ

            // Thêm ảnh đại diện vào danh sách ảnh nhỏ đầu tiên
            let galleryHtml = `<a href="javascript:;" class="active change-main-image" data-big="${thumbnail}"><img src="${thumbnail}" alt="Thumbnail"></a>`;

            // Nếu có các ảnh khác trong gallery thì thêm vào
            if (info.images && info.images.length > 0) {
                info.images.forEach((img) => {
                    galleryHtml += `<a href="javascript:;" class="change-main-image" data-big="${img.url}"><img src="${img.url}" alt="Gallery"></a>`;
                });
            }
            $galleryContainer.html(galleryHtml);

            // Sự kiện click ảnh nhỏ đổi ảnh to
            const self = this;
            $modal.find(".change-main-image").on("click", function (e) {
                e.preventDefault();
                const newSrc = $(this).data("big");

                // Đổi ảnh to
                $modal.find(".product-main-image img").attr("src", newSrc);

                // Xử lý Active class
                $modal.find(".change-main-image").removeClass("active");
                $(this).addClass("active");

                // Reset Zoom nếu có dùng
                if ($.fn.zoom) {
                    $modal
                        .find(".product-main-image")
                        .trigger("zoom.destroy")
                        .zoom({ url: newSrc });
                }
            });
            // --- HẾT PHẦN XỬ LÝ GALLERY ---

            // Đổ dữ liệu text như cũ
            $modal.find(".product-main-image img").attr("src", thumbnail);
            $modal.find("h1").text(info.name);
            $modal
                .find(".description p")
                .text(info.description || "Chưa có mô tả.");

            // Xử lý giá
            const hasSale =
                pricing.sale_price > 0 &&
                pricing.sale_price < pricing.original_price;
            const displayPrice = hasSale
                ? pricing.sale_price
                : pricing.original_price;
            $modal.find(".price strong").html(this.formatPrice(displayPrice));

            if (hasSale) {
                $modal
                    .find(".price em")
                    .html(this.formatPrice(pricing.original_price))
                    .show();
                $modal.find(".sticker-sale").show();
            } else {
                $modal.find(".price em").hide();
                $modal.find(".sticker-sale").hide();
            }

            $modal
                .find(".availability strong")
                .text(inventory.in_stock ? "In Stock" : "Out of Stock");
            $modal
                .find(".btn-default[href]")
                .attr("href", `/products/${info.slug}`);

            if (typeof $.fancybox !== "undefined") {
                $.fancybox.open($modal, {
                    type: "inline",
                    autoSize: false,
                    width: 700,
                    afterShow: function () {
                        if (typeof Layout !== "undefined") {
                            Layout.initTouchspin();
                        }
                        // Kích hoạt zoom cho ảnh đầu tiên khi mở modal
                        if ($.fn.zoom) {
                            $modal
                                .find(".product-main-image")
                                .zoom({ url: thumbnail });
                        }
                    },
                });
            }
        } catch (error) {
            console.error("Lỗi Quick View:", error);
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
    new ProductList();
});
