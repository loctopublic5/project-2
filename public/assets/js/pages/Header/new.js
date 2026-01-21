$(document).ready(function() {
    function initNewArrivalMenu() {
        $.ajax({
            url: '/api/v1/products',
            type: 'GET',
            data: {
                limit: 3,
                sort_by: 'latest'
            },
            success: function(res) {
                // Mapping dựa trên cấu trúc: res.data (mảng các ProductResource)
                const products = res.data; 
                let html = '';

                products.forEach(prod => {
                    // 1. Lấy Thumbnail từ info.thumbnail
                    const thumbnail = prod.info.thumbnail || 'assets/pages/img/products/model4.jpg';
                    
                    // 2. Lấy Tên và Link từ info
                    const productName = prod.info.name;
                    const productLink = `/product/${prod.info.slug}`;
                    
                    // 3. Xử lý Giá từ pricing (Ưu tiên sale_price nếu is_sale_active = true)
                    let displayPrice = '';
                    if (prod.pricing.is_sale_active) {
                        displayPrice = `
                            <span class="pi-price" style="color: #e84d1c;">${formatVND(prod.pricing.sale_price)}</span>
                            <del style="font-size: 11px; margin-left: 5px;">${formatVND(prod.pricing.original_price)}</del>
                        `;
                    } else {
                        displayPrice = `<span class="pi-price">${formatVND(prod.pricing.original_price)}</span>`;
                    }

                    // 4. Render HTML
                    html += `
                        <div class="col-md-4 col-sm-4 col-xs-6">
                            <div class="product-item">
                                <div class="pi-img-wrapper">
                                    <a href="${productLink}">
                                        <img src="${thumbnail}" class="img-responsive" alt="${productName}" style="height: 150px; object-fit: cover; width: 100%;">
                                    </a>
                                </div>
                                <h3 style="height: 32px; overflow: hidden; margin-top: 10px;">
                                    <a href="${productLink}">${productName}</a>
                                </h3>
                                <div class="price-wrapper">
                                    ${displayPrice}
                                </div>
                                <div class="inventory-status" style="font-size: 10px; color: ${prod.inventory.in_stock ? 'green' : 'red'}">
                                    <i class="fa fa-circle"></i> ${prod.inventory.status_text}
                                </div>
                            </div>
                        </div>`;
                });

                $('#js-new-arrivals').html(html);
            },
            error: function(err) {
                console.error("Lỗi API Product:", err);
                $('#js-new-arrivals').html('<div class="col-md-12">Không thể tải sản phẩm mới.</div>');
            }
        });
    }

    // Hàm bổ trợ định dạng tiền Việt
    function formatVND(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }

    initNewArrivalMenu();
});