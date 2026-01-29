/**
 * Shopping Cart Module - Senior Version
 */
const ShoppingCart = {
    apiBase: '/api/v1/customer/cart',

    init: function() {
        console.log("🛒 ShoppingCart Module Initializing...");
        this.bindEvents();
        this.loadCart();
    },

    // Hàm thông báo cho Mini Cart ở Header
    refreshMiniCart: function() {
        window.dispatchEvent(new CustomEvent('cart:updated'));
    },

    loadCart: async function() {
        try {
            const response = await window.api.get(this.apiBase);
            const result = response.data; 
            
            if (result.status === true) { 
                this.renderCart(result.data);
            }
        } catch (error) {
            console.error('❌ Error loading cart:', error);
        }
    },

    renderCart: function(data) {
        const $container = $('#cart-items-container');
        const items = data.items || [];
        
        if (items.length === 0) {
            $container.html('<tr><td colspan="7" class="text-center">Giỏ hàng trống!</td></tr>');
            this.updateSummary(data.summary);
            return;
        }

        let html = '';
        items.forEach(item => {
            const info = item.product_info;
            let optionsHtml = '';
            if (item.options && !Array.isArray(item.options)) {
                optionsHtml = Object.entries(item.options)
                    .map(([k, v]) => `<small class="label label-default" style="font-weight:normal; margin-right:3px;">${k}: ${v}</small>`)
                    .join('');
            }
            
            html += `
            <tr data-item-id="${item.item_id}">
                <td class="goods-page-image">
                    <a href="javascript:;" class="edit-cart-item" data-product-id="${item.product_id}" data-item-id="${item.item_id}">
                        <img src="${info.avatar}" alt="${info.name}">
                    </a>
                </td>
                <td class="goods-page-description">
                    <h3>
                        <a href="javascript:;" class="edit-cart-item" data-product-id="${item.product_id}" data-item-id="${item.item_id}">
                            ${info.name}
                        </a>
                    </h3>
                    <p><strong>SKU:</strong> ${info.sku}</p>
                    <div class="cart-item-options">${optionsHtml}</div>
                    ${item.is_error ? `<em class="text-danger">${item.error_message}</em>` : ''}
                </td>
                <td class="goods-page-ref-no">${info.sku}</td>
                <td class="goods-page-quantity">
                    <div class="product-quantity">
                        <input type="text" value="${item.quantity}" readonly 
                            class="form-control input-sm cart-qty-input" 
                            data-max="${item.max_qty}">
                    </div>
                </td>
                <td class="goods-page-price">
                    <strong><span>$</span>${item.price.toLocaleString()}</strong>
                </td>
                <td class="goods-page-total"><strong><span>$</span>${item.line_total.toLocaleString()}</strong></td>
                <td class="del-goods-col">
                    <a class="del-goods btn-delete-item" href="javascript:;" data-id="${item.item_id}">&nbsp;</a>
                </td>
            </tr>`;
        });

        $container.html(html);
        this.updateSummary(data.summary);
        if (typeof Layout !== 'undefined') { Layout.initTouchspin(); }
    },

    updateSummary: function(summary) {
        if (!summary) return;
        $('#sub-total').text(`$${summary.subtotal.toLocaleString()}`);
        $('#shipping-fee').text(`$${summary.shipping_fee.toLocaleString()}`);
        $('#final-total').text(`$${summary.final_total.toLocaleString()}`);
    },

    bindEvents: function() {
        const self = this;

        // 1. Thay đổi số lượng trực tiếp
        $(document).on('change', '.cart-qty-input', function() {
            const itemId = $(this).closest('tr').data('item-id');
            const qty = $(this).val();
            self.updateQuantity(itemId, qty);
        });

        // 2. Xóa sản phẩm
        $(document).on('click', '.btn-delete-item', function() {
            const itemId = $(this).data('id');
            self.handleDelete(itemId);
        });
        
        // 3. Mở Quick View để sửa
        $(document).on('click', '.edit-cart-item', function(e) {
            e.preventDefault();
            const productId = $(this).data('product-id');
            const itemId = $(this).data('item-id');
            self.openQuickView(productId, itemId);
        });

        // 4. Click đổi ảnh trong Modal
        $(document).on('click', '.change-main-image', function() {
            const newImg = $(this).data('image');
            $('#modal-product-image').attr('src', newImg);
            $('.product-other-images a').removeClass('active');
            $(this).addClass('active');
        });

        // 5. Nút Cập nhật trong Modal
        $(document).on('click', '#btn-modal-update-cart', async function() {
            const itemId = $(this).data('item-id');
            const quantity = $('#modal-product-quantity').val();
            const options = {};
            $('.select-attribute').each(function() {
                options[$(this).data('key')] = $(this).val();
            });

            try {
                const response = await window.api.put(`/api/v1/customer/cart/${itemId}`, {
                    quantity: quantity,
                    options: options
                });

                if (response.data.status) {
                    $.fancybox.close();
                    self.loadCart();
                    self.refreshMiniCart(); // Update Header
                    Swal.fire('Thành công', 'Giỏ hàng đã cập nhật', 'success');
                }
            } catch (error) {
                Swal.fire('Lỗi', error.response?.data?.message || 'Không thể cập nhật', 'error');
            }
        });
    },

    updateQuantity: async function(itemId, quantity) {
        try {
            const response = await window.api.put(`${this.apiBase}/${itemId}`, { quantity });
            if (response.data.status) {
                this.loadCart();
                this.refreshMiniCart();
            }
        } catch (error) {
            this.loadCart();
        }
    },

    handleDelete: function(itemId) {
        Swal.fire({
            title: 'Xóa khỏi giỏ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await window.api.delete(`${this.apiBase}/${itemId}`);
                    if (response.data.status) {
                        this.loadCart();
                        this.refreshMiniCart();
                    }
                } catch (error) {
                    Swal.fire('Lỗi', "Không thể xóa", 'error');
                }
            }
        });
    },

    openQuickView: async function(productId, itemId) {
        try {
            const response = await window.api.get(`/api/v1/products/${productId}`);
            if (response.data.status) {
                this.fillModal(response.data.data, itemId);
                $.fancybox({
                    href: '#product-pop-up',
                    afterShow: function() { Layout.initTouchspin(); }
                });
            }
        } catch (error) {
            console.error("Lỗi:", error);
        }
    },

    fillModal: function(product, itemId) {
        const info = product.info;
        const pricing = product.pricing;

        $('#modal-product-name').text(info.name);
        $('#modal-product-image').attr('src', info.thumbnail);
        $('#modal-product-desc').html(info.description);
        $('#modal-product-status').text(product.inventory.status_text);
        $('#btn-modal-update-cart').data('item-id', itemId);

        $('#modal-product-price').text(`$${pricing.sale_price.toLocaleString()}`);
        if (pricing.is_sale_active) {
            $('#modal-product-old-price').html(`$${pricing.original_price.toLocaleString()}`).show();
        } else {
            $('#modal-product-old-price').hide();
        }

        let galleryHtml = `<a href="javascript:;" class="active change-main-image" data-image="${info.thumbnail}"><img src="${info.thumbnail}"></a>`;
        if (info.images) {
            info.images.forEach(img => {
                galleryHtml += `<a href="javascript:;" class="change-main-image" data-image="${img.url}"><img src="${img.url}"></a>`;
            });
        }
        $('#modal-product-gallery').html(galleryHtml);

        let attrHtml = '';
        if (product.specifications) {
            Object.entries(product.specifications).forEach(([label, values]) => {
                if (Array.isArray(values)) {
                    attrHtml += `
                        <div class="pull-left" style="margin-right: 15px; margin-bottom: 10px;">
                            <label class="control-label">${label}:</label>
                            <select class="form-control input-sm select-attribute" data-key="${label}">
                                ${values.map(v => `<option value="${v}">${v}</option>`).join('')}
                            </select>
                        </div>`;
                }
            });
        }
        $('#modal-product-attributes').html(attrHtml);
    }
};