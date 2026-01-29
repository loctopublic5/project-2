// public/assets/js/pages/Huy/products-list.js

class ProductList {
    constructor() {
        this.currentPage = 1;
        this.limit = 9;
        this.isLoading = false;
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

    init() {
        // 1. Chỉ nạp Categories và Bestsellers trước
        this.loadCategories();
        this.loadBestsellers();

        // 2. Gán sự kiện (Chỉ gán 1 lần duy nhất)
        this.initEventListeners();

        // 3. Nạp sản phẩm lần đầu
        this.loadProducts(1);
    }

    async loadCategories() {
        try {
            const response = await window.api.get("/api/v1/categories");
            if (response.data?.status) {
                // Check status cho chắc
                this.renderCategories(response.data.data);
            }
        } catch (error) {
            console.error("Lỗi nạp danh mục:", error);
        }
    }

    renderCategories(categories) {
        const container = $("#sidebar-categories");
        let html = "";

        categories.forEach((cat) => {
            const hasChild = cat.children && cat.children.length > 0;

            html += `
        <li class="list-group-item clearfix dropdown">
            <a href="javascript:void(0);" class="category-link" data-id="${cat.id}">
                ${hasChild ? '<i class="fa fa-angle-right toggle-icon"></i>' : '<i class="fa fa-angle-right"></i>'} 
                ${cat.name}
            </a>
            ${hasChild ? this.renderSubFromTree(cat.children) : ""}
        </li>`;
        });

        container.html(html);
    }

    renderSubFromTree(subCategories) {
        // Mặc định ẩn sub-menu để giống trạng thái ban đầu của template
        let subHtml =
            '<ul class="sub-menu" style="display:none; list-style:none; padding-left: 20px;">';

        subCategories.forEach((sub) => {
            const hasChild = sub.children && sub.children.length > 0;
            subHtml += `
        <li style="line-height: 30px;">
            <a href="javascript:void(0);" class="category-link" data-id="${sub.id}" style="text-decoration:none; color:#666; display: block;">
                <i class="fa fa-angle-right toggle-icon" 
                   style="${hasChild ? "cursor:pointer; width: 15px;" : "visibility:hidden; width: 15px;"}"></i>
                ${sub.name}
            </a>
            ${hasChild ? this.renderSubFromTree(sub.children) : ""}
        </li>`;
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

        // 1. Sắp xếp (Sử dụng Event Delegation để không bị lặp)
        $(document)
            .off("change", ".list-view-sorting select:eq(1)")
            .on("change", ".list-view-sorting select:eq(1)", function () {
                const val = $(this).val();

                if (val.includes("p.price&order=ASC")) {
                    self.sort = { field: "price", order: "asc" };
                } else if (val.includes("p.price&order=DESC")) {
                    self.sort = { field: "price", order: "desc" };
                } else {
                    self.sort = { field: "latest", order: "desc" };
                }
                self.loadProducts(1); // Luôn về trang 1 khi đổi sort
            });

        // 2. Giới hạn hiển thị
        $(document)
            .off("change", ".list-view-sorting select:eq(0)")
            .on("change", ".list-view-sorting select:eq(0)", function () {
                const val = $(this).val();
                if (val.includes("limit=")) {
                    self.limit = parseInt(val.split("=")[1]);
                    self.loadProducts(1); // Luôn về trang 1 khi đổi limit
                }
            });

        // 3. Phân trang (Bổ sung để dứt điểm lỗi nhảy trang)
        $(document)
            .off("click", "#product-pagination a")
            .on("click", "#product-pagination a", function (e) {
                e.preventDefault();
                const page = $(this).data("page");
                if (page) self.loadProducts(page);
            });

        // 4. Chuyển đổi view (Grid/List)
        $(document)
            .off("click", ".list-view a")
            .on("click", ".list-view a", function (e) {
                e.preventDefault();
                $(".list-view a").removeClass("active");
                $(this).addClass("active");
                const isGrid = $(this).find(".fa-th-large").length > 0;
                $("#real-product-container").toggleClass(
                    "list-view-mode",
                    !isGrid,
                );
            });

        // 5. Danh mục (Đã sửa .off() chuẩn)
        $(document)
            .off("click", ".category-link")
            .on("click", ".category-link", (e) => {
                e.preventDefault();
                const catId = $(e.currentTarget).data("id");
                this.filters.category_id = catId;
                this.loadProducts(1);
            });

        // 6. Icon đóng mở (Đã sửa .off() chuẩn)
        $(document)
            .off("click", ".toggle-icon")
            .on("click", ".toggle-icon", function (e) {
                e.preventDefault();
                e.stopPropagation();
                const $icon = $(this);
                const $parentLi = $icon.closest("li");
                $parentLi.children("ul.sub-menu").slideToggle(300);
                $icon.toggleClass("open");
                const rotate = $icon.hasClass("open") ? "90deg" : "0deg";
                $icon.css("transform", `rotate(${rotate})`);
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
        console.log(`--- đang gọi request trang: ${page} ---`);
        if (this.loading) return;
        this.loading = true;

        try {
            this.currentPage = page;
            const catIdValue = this.filters.category_id || "";

            const params = {
                page: this.currentPage,
                limit: this.limit,
                category_id: catIdValue,
                min_price: this.filters.minPrice,
                max_price: this.filters.maxPrice,
                sort_by:
                    this.sort.field === "price"
                        ? `price_${this.sort.order}`
                        : "latest",
            };

            const response = await window.api.get("/api/v1/products", {
                params,
            });

            if (response.data.status === true) {
                const products = response.data.data;

                this.products = products;
                console.log("Danh sách sản phẩm nhận được:", products);

                this.renderProducts(products);
                this.renderPagination(response.data.meta);
            } else {
                // Trường hợp backend trả về cấu trúc khác
                this.renderPagination({
                    current_page: response.data.current_page || 1,
                    total_pages: response.data.total_pages || 1,
                    total_items: response.data.total_items || 0,
                });
            }
        } catch (error) {
            console.error("Lỗi tải sản phẩm:", error);
        } finally {
            // QUAN TRỌNG NHẤT: Phải có dòng này thì lần sau mới click được tiếp
            this.loading = false;
            console.log("Đã mở khóa loading!");
        }
    }
    async loadCategories() {
        try {
            const response = await window.api.get("/api/v1/categories");
            if (response.data?.status) {
                // Check status cho chắc
                this.renderCategories(response.data.data);
            }
        } catch (error) {
            console.error("Lỗi nạp danh mục:", error);
        }
    }

    async loadBestsellers() {
        try {
            const params = { limit: 4, sort_by: "latest" };
            const response = await window.api.get("/api/v1/products", {
                params,
            });
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
    // Cập nhật lại hàm trong class ProductList
    addToCart(productId, options = {}) {
    const token = localStorage.getItem("admin_token");
    if (!token) {
        Swal.fire('Thông báo', 'Bạn cần đăng nhập để thực hiện chức năng này', 'info')
            .then(() => window.location.href = "/login");
        return;
    }

    const bodyData = {
        product_id: parseInt(productId),
        quantity: parseInt(options.quantity || 1),
        options: options.options || {} 
    };

    const $btn = options.event ? $(options.event.currentTarget) : $(`.js-add-to-cart[data-id="${productId}"]`);
    const originalText = $btn.html();
    $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop("disabled", true);

    window.api.post("/api/v1/customer/cart", bodyData)
        .then((res) => {
            if ($.fancybox) $.fancybox.close();
            
            // Hiển thị Toast thành công (Dựa trên ID #cart-success-toast bạn đã tạo)
            const $toast = $("#cart-success-toast");
            $toast.addClass('show').fadeIn(300);
            
            setTimeout(() => {
                $toast.fadeOut(300, function() { $(this).removeClass('show'); });
            }, 2500);

            // Cập nhật giỏ hàng trên Header (Event listener)
            window.dispatchEvent(new Event("cart:updated"));
        })
        .catch((err) => {
    // 1. Lấy message từ server
    let serverMsg = err.response?.data?.message || "";
    let friendlyMsg = "Lỗi khi thêm vào giỏ hàng!";

    // 2. Kiểm tra nếu là lỗi Duplicate (trùng sản phẩm)
    if (serverMsg.includes("Duplicate entry") || serverMsg.includes("23000")) {
        friendlyMsg = "Sản phẩm này đã có trong giỏ hàng. Bạn có thể vào Giỏ hàng để điều chỉnh số lượng!";
        
        // Hiển thị thông báo dạng Warning (Vàng) cho tinh tế
        Swal.fire({
            title: 'Sản phẩm đã tồn tại',
            text: friendlyMsg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e84d1c',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Xem Giỏ Hàng',
            cancelButtonText: 'Ở lại đây'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "/cart"; // Link tới trang giỏ hàng của bạn
            }
        });
    } else {
        // Các lỗi khác (hết hạn token, server die...)
        Swal.fire('Thất bại', serverMsg || friendlyMsg, 'error');
    }
})
        .finally(() => {
            $btn.html(originalText).prop("disabled", false);
        });
}

    createProductHTML(product) {
    const { pricing, info, inventory } = product;
    const hasSale = pricing.sale_price > 0 && pricing.sale_price < pricing.original_price;
    const displayPrice = hasSale ? pricing.sale_price : pricing.original_price;
    const thumbnail = info.thumbnail || "assets/pages/img/products/model1.jpg";

    // Chỉ trả về card, không bọc col-md ở đây vì Blade đã có container
    return `
    <div class="product-grid-item">
        <div class="product-item">
            <div class="pi-img-wrapper">
                <img src="${thumbnail}" class="img-responsive" alt="${info.name}">
                <div class="pi-img-btns">
                    <a href="${thumbnail}" class="btn btn-default fancybox-button">Zoom</a>
                    <a href="javascript:void(0);" class="btn btn-default quick-view" data-id="${product.id}">View</a>
                </div>
            </div>
            <h3><a href="/products/${info.slug}">${info.name}</a></h3>
            <div class="pi-price">
                ${this.formatPrice(displayPrice)}
                ${hasSale ? `<span class="old-price" style="text-decoration: line-through; color: #bbb; margin-left: 8px; font-weight: normal; font-size: 14px;">${this.formatPrice(pricing.original_price)}</span>` : ""}
            </div>
            
            <a href="javascript:void(0);" 
               class="btn btn-default add2cart js-add-to-cart" 
               data-id="${product.id}" 
               ${!inventory.in_stock ? "disabled" : ""}>
                <i class="fa fa-shopping-cart"></i> Add to cart
            </a>

            ${hasSale ? `<div class="sticker sticker-sale"></div>` : ""}
            ${!inventory.in_stock ? '<div class="sticker sticker-out-of-stock" style="background: #999; color: #fff; text-align:center; line-height:40px;">HẾT</div>' : ""}
        </div>
    </div>`;
}
    formatPrice(price) {
        return new Intl.NumberFormat("vi-VN").format(price) + " ₫";
    }

    initProductEvents() {
    const self = this;

    // Sự kiện Quick View
    $(document).off("click", ".quick-view").on("click", ".quick-view", function () {
        const productId = $(this).data("id");
        self.showQuickView(productId);
    });

    // Sự kiện Add to Cart ngoài danh sách
    $(document).off("click", ".js-add-to-cart").on("click", ".js-add-to-cart", function (e) {
        e.preventDefault();
        const productId = $(this).data("id");

        // 1. Tìm thông tin sản phẩm từ mảng dữ liệu đã lưu khi fetch
        // Giả sử mảng sản phẩm của bạn là this.currentProducts
        const product = self.currentProducts ? self.currentProducts.find(p => p.id == productId) : null;

        let selectedOptions = {};

        // 2. Nếu tìm thấy sản phẩm và có specifications, lấy giá trị đầu tiên của mỗi loại
        if (product && product.specifications) {
            Object.entries(product.specifications).forEach(([label, values]) => {
                if (Array.isArray(values) && values.length > 0) {
                    selectedOptions[label] = values[0];
                }
            });
        }

        // 3. Gọi hàm addToCart với đầy đủ params
        self.addToCart(productId, {
            quantity: 1,
            options: selectedOptions,
            event: e
        });
    });

    // Fancybox
    if (typeof $.fancybox !== "undefined") {
        $(".fancybox-button").fancybox();
    }
}

    async showQuickView(productId) {
    try {
        const response = await axios.get(`/api/v1/products/${productId}`);
        const product = response.data.data;
        
        // Destructure đúng các key từ JSON của bạn
        const { id, info, pricing, inventory, specifications } = product;

        const $modal = $("#product-pop-up");
        const thumbnail = info.thumbnail || "assets/pages/img/products/model7.jpg";

        // 1. Render Gallery & Ảnh chính
        const $galleryContainer = $("#modal-product-gallery");
        $galleryContainer.empty();
        
        let galleryHtml = `<div class="thumb-item active" data-big="${thumbnail}"><img src="${thumbnail}"></div>`;
        if (info.images && info.images.length > 0) {
            info.images.forEach(img => {
                galleryHtml += `<div class="thumb-item" data-big="${img.url}"><img src="${img.url}"></div>`;
            });
        }
        $galleryContainer.html(galleryHtml);
        $("#modal-product-image").attr("src", thumbnail);

        // Đổi ảnh khi click thumb (Sửa lại selector cho khớp với class thumb-item)
        $galleryContainer.find(".thumb-item").on("click", function () {
            const newSrc = $(this).data("big");
            $("#modal-product-image").attr("src", newSrc);
            $galleryContainer.find(".thumb-item").removeClass("active");
            $(this).addClass("active");
        });

        // 2. Mapping Thông tin cơ bản
        $("#modal-product-name").text(info.name);
        $("#modal-product-desc").text(info.description || "Chưa có mô tả.");
        
        // Hiển thị số lượng tồn kho thực tế từ inventory.stock_qty
        const statusHtml = `${inventory.status_text} <span class="stock-count" style="font-weight: normal; font-size: 12px;">(Kho: ${inventory.stock_qty})</span>`;
        $("#modal-product-status").html(statusHtml)
                                  .css('color', inventory.in_stock ? '#3e9c35' : '#d9534f');

        // 3. Xử lý Giá (Mapping từ key 'pricing')
        const displayPrice = pricing.is_sale_active && pricing.sale_price > 0 
                            ? pricing.sale_price 
                            : pricing.original_price;
        
        $("#modal-product-price").html(this.formatPrice(displayPrice));
        if (pricing.is_sale_active && pricing.sale_price < pricing.original_price) {
            $("#modal-product-old-price").html(this.formatPrice(pricing.original_price)).show();
        } else {
            $("#modal-product-old-price").hide();
        }

        // 4. MAPPING OPTIONS (TỪ specifications)
        const $attrContainer = $("#modal-product-attributes");
        $attrContainer.empty();

        if (specifications && Object.keys(specifications).length > 0) {
    let specHtml = '';
    Object.entries(specifications).forEach(([label, values]) => {
        specHtml += `
            <div class="option-row">
                <label>${label}</label>
                <select class="form-control js-modal-variant" data-attr="${label}">
                    ${values.map(v => `<option value="${v}">${v}</option>`).join('')}
                </select>
            </div>`;
    });
    $attrContainer.html(specHtml);
}

        // 5. THIẾT LẬP SỐ LƯỢNG VÀ NÚT BẤM
        const $qtyInput = $("#modal-product-quantity");
        const $btnAddCart = $("#btn-modal-add-to-cart");
        
        // Reset về 1 hoặc 0 tùy tình trạng kho
        $qtyInput.val(inventory.stock_qty > 0 ? 1 : 0);
        $btnAddCart.attr("data-item-id", id);

        // Disable nút nếu hết hàng
        if (!inventory.in_stock || inventory.stock_qty <= 0) {
            $btnAddCart.prop('disabled', true).addClass('disabled').text('HẾT HÀNG');
        } else {
            $btnAddCart.prop('disabled', false).removeClass('disabled').text('Add To Cart');
        }

        // 6. HIỂN THỊ MODAL (FANCYBOX)
        const self = this;
        if (typeof $.fancybox !== "undefined") {
            $.fancybox.open($modal, {
                type: "inline",
                autoSize: false,
                width: 700,
                afterShow: function () {
                    // Khởi tạo Touchspin với giới hạn thực tế từ API
                    if (typeof Layout !== "undefined") {
                        $qtyInput.trigger("touchspin.destroy"); 
                        $qtyInput.TouchSpin({
                            buttondown_class: "btn btn-default",
                            buttonup_class: "btn btn-default",
                            min: inventory.stock_qty > 0 ? 1 : 0,
                            max: inventory.stock_qty, // Mapping số lượng 180 vào đây
                            step: 1
                        });
                    }

                    // Xử lý sự kiện nút Add To Cart
                    $btnAddCart.off("click").on("click", function(e) {
                        e.preventDefault();
                        const $btn = $(this);
                        
                        const qty = parseInt($qtyInput.val()) || 1;

                        // Kiểm tra lại số lượng trước khi gửi
                        if (qty > inventory.stock_qty) {
                            Swal.fire('Lỗi', `Chỉ còn ${inventory.stock_qty} sản phẩm trong kho`, 'error');
                            return;
                        }

                        // Thu thập các option đã chọn
                        let options = {};
                        $(".js-modal-variant").each(function() {
                            options[$(this).data('attr')] = $(this).val();
                        });

                        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Adding...');

                        // Gọi hàm addToCart (Unique productId logic nằm trong hàm này của bạn)
                        self.addToCart(id, {
                            quantity: qty,
                            options: options,
                            onDone: () => {
                                $btn.prop('disabled', false).html('Add To Cart');
                                $.fancybox.close();
                                // Hiển thị toast thành công nếu bạn có hàm này
                                if (typeof self.showCartToast === 'function') {
                                    self.showCartToast();
                                }
                            }
                        });
                    });
                }
            });
        }

    } catch (error) {
        console.error("Lỗi fetch sản phẩm:", error);
        if (typeof Swal !== "undefined") {
            Swal.fire('Lỗi', 'Không thể tải thông tin sản phẩm', 'error');
        }
    }
}
}

// Khởi tạo
$(document).ready(() => {
    // Đảm bảo chỉ khởi tạo 1 lần
    if (!window.huyProductApp) {
        window.huyProductApp = new ProductList();
        window.huyProductApp.init();
    }
});
