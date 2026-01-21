const API_URL = '/api/v1/admin/products';
const API_CATEGORIES = '/api/v1/admin/categories';
let productModal; 
let deletedImageIds = [];
let selectedGalleryFiles = []; // Lưu các File object mới chọn từ input gallery


// Init
document.addEventListener('DOMContentLoaded', function () {
    // Khởi tạo Modal Bootstrap
    const modalEl = document.getElementById('productModal');
    if(modalEl) {
        productModal = new bootstrap.Modal(modalEl);
    }
    
    loadCategories(); 
    loadProducts();  
});

const formatMoney = (amount) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}

// --- 1. LOAD DATA ---

async function loadCategories() {
    try {
        const res = await window.api.get(API_CATEGORIES);
        const categories = res.data.data; 
        
        // 1. Fill vào Select trong Modal (Form thêm/sửa)
        const selectModal = document.getElementById('category_id');
        if(selectModal) {
            selectModal.innerHTML = '<option value="">-- Chọn danh mục --</option>';
            categories.forEach(cat => {
                selectModal.insertAdjacentHTML('beforeend', `<option value="${cat.id}">${cat.name}</option>`);
            });
        }

        // 2. Fill vào Select Bộ lọc (Ngoài danh sách)
        const selectFilter = document.getElementById('filter-category');
        if(selectFilter) {
            // Giữ option đầu tiên (-- Tất cả --)
            selectFilter.innerHTML = '<option value="">-- Tất cả danh mục --</option>';
            categories.forEach(cat => {
                selectFilter.insertAdjacentHTML('beforeend', `<option value="${cat.id}">${cat.name}</option>`);
            });
        }

    } catch (error) {
        console.error("Lỗi load danh mục", error);
    }
}

// Hàm load sản phẩm (Đã update lấy params từ bộ lọc)
window.loadProducts = async function(page = 1) {
    const keyword = document.getElementById('search-input').value;
    const categoryId = document.getElementById('filter-category').value;
    const status = document.getElementById('filter-status').value; // 1 hoặc 0
    const sortBy = document.getElementById('sort-by').value;

    const tbody = document.getElementById('product-list-body');
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>';

    try {
        // Gửi params lên server
        const params = { 
            page, 
            keyword,
            category_id: categoryId,
            is_active: status,
            sort_by: sortBy
        };

        const res = await window.api.get(API_URL, { params });
        const products = res.data.data;
        const meta = res.data.meta;

        tbody.innerHTML = '';
        if (!products || products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Không tìm thấy sản phẩm nào</td></tr>';
            return;
        }

        products.forEach(p => {
            // FIX ẢNH: Kiểm tra kỹ thumbnail
            let thumbnail = '/assets/static/images/no-image.png'; // Ảnh mặc định frontend
            if (p.info.thumbnail) {
                // Nếu backend trả về full url (http...) thì dùng luôn
                // Nếu trả về path (storage/...) thì nối thêm domain (tuỳ logic backend Resource của bạn)
                // Resource của bạn dùng Storage::url() nên thường nó trả về /storage/path...
                thumbnail = p.info.thumbnail;
            }

            const name = p.info.name;
            const sku = p.info.sku;
            const categoryName = p.category ? p.category.name : '---';
            
            // Pricing
            const originalPrice = p.pricing.original_price;
            const salePrice = p.pricing.sale_price;
            const isSaleActive = p.pricing.is_sale_active;

            // Inventory
            const stockQty = p.inventory.stock_qty;
            const stockStatus = p.inventory.status_text; 

            const isActive = p.is_active; 
            const id = p.id;

            const statusBadge = isActive 
                ? '<span class="badge bg-success">Đang bán</span>' 
                : '<span class="badge bg-secondary">Tạm ẩn</span>';

            const priceHtml = isSaleActive
                ? `<div><del class="text-muted small">${formatMoney(originalPrice)}</del><br><span class="text-danger fw-bold">${formatMoney(salePrice)}</span></div>`
                : `<span class="fw-bold">${formatMoney(originalPrice)}</span>`;

            const row = `
                <tr>
                    <td>
                        <img src="${thumbnail}" width="50" height="50" class="rounded border" style="object-fit: cover" 
                             onerror="this.src='/assets/static/images/no-image.png'"> </td>
                    <td>
                        <div class="fw-bold text-truncate" style="max-width: 200px;" title="${name}">
                            <a href="#" onclick="editProduct(${id}); return false;">${name}</a>
                        </div>
                        <small class="text-muted">SKU: ${sku}</small>
                    </td>
                    <td>${categoryName}</td>
                    <td>${priceHtml}</td>
                    <td class="text-center">
                        <div>${stockQty}</div>
                        <small class="text-muted" style="font-size: 10px">${stockStatus}</small>
                    </td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary" onclick="editProduct(${id})"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-sm btn-danger ms-1" onclick="deleteProduct(${id})"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
        
        if(typeof renderPagination === 'function') renderPagination(meta);
        renderPagination(res.data.meta);

    } catch (error) {
        console.error("Lỗi load sản phẩm:", error);
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Lỗi kết nối server</td></tr>';
    }
}

function renderPagination(meta) {
    const paginationDiv = document.getElementById('pagination-links');
    const infoDiv = document.getElementById('pagination-info');
    
    // 1. Text Info (Màu text-muted tự động thích ứng Dark/Light)
    if (infoDiv) {
        if (meta.total > 0) {
            infoDiv.innerHTML = `Hiển thị <b>${meta.from}</b> - <b>${meta.to}</b> / <b>${meta.total}</b>`;
        } else {
            infoDiv.innerHTML = '<span class="text-muted fst-italic">Không có dữ liệu</span>';
        }
    }

    // 2. Nút bấm
    if (!paginationDiv) return;
    
    if (meta.last_page <= 1) {
        paginationDiv.innerHTML = '';
        return;
    }

    // Thêm class 'pagination-primary' để ăn theo màu theme Mazer
    let html = '<nav aria-label="Page navigation"><ul class="pagination pagination-primary justify-content-end mb-0">';
    
    // Previous
    const prevDisabled = meta.current_page === 1 ? 'disabled' : '';
    html += `<li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadProducts(${meta.current_page - 1})">
                    <i class="bi bi-chevron-left"></i>
                </a>
             </li>`;

    // Logic rút gọn số trang (1 ... 4 5 6 ... 10)
    const current = meta.current_page;
    const last = meta.last_page;
    const delta = 1; 
    const range = [];
    const rangeWithDots = [];
    let l;

    for (let i = 1; i <= last; i++) {
        if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
            range.push(i);
        }
    }

    for (const i of range) {
        if (l) {
            if (i - l === 2) {
                rangeWithDots.push(l + 1);
            } else if (i - l !== 1) {
                rangeWithDots.push('...');
            }
        }
        rangeWithDots.push(i);
        l = i;
    }

    rangeWithDots.forEach(page => {
        if (page === '...') {
            // Dùng text-muted cho dấu ...
            html += `<li class="page-item disabled"><span class="page-link text-muted" style="background: transparent; border: none;">...</span></li>`;
        } else {
            const active = page === current ? 'active' : '';
            html += `<li class="page-item ${active}">
                        <a class="page-link" href="#" onclick="event.preventDefault(); loadProducts(${page})">${page}</a>
                     </li>`;
        }
    });

    // Next
    const nextDisabled = current === last ? 'disabled' : '';
    html += `<li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadProducts(${current + 1})">
                    <i class="bi bi-chevron-right"></i>
                </a>
             </li>`;

    html += '</ul></nav>';
    paginationDiv.innerHTML = html;
}

/**
 * Quét toàn bộ bảng để lấy dữ liệu attribute
 * return Array
 */
function collectAttributes() {
    const attributes = [];
    
    // 1. Tìm tất cả các dòng có class 'attribute-item'
    const rows = document.querySelectorAll('.attribute-item');
    
    rows.forEach(row => {
        // 2. Lấy input con của dòng đó
        const nameInput = row.querySelector('.attr-name');
        const valueInput = row.querySelector('.attr-value');
        
        // 3. Chỉ lấy khi có nhập tên (Giá trị có thể rỗng tùy logic của bạn)
        if (nameInput && nameInput.value.trim() !== '') {
            attributes.push({
                name: nameInput.value.trim(),
                value: valueInput ? valueInput.value.trim() : ''
            });
        }
    });

    return attributes;
}

async function editProduct(id) {
    // 1. Dọn dẹp sạch sẽ trước khi nạp data mới
    window.resetForm();
    
    // 2. Hiện Modal trước để các Element "sẵn sàng" nhận data
    if(productModal) productModal.show();
    document.getElementById('modalTitle').innerText = 'Đang tải dữ liệu...';

    try {
        const res = await window.api.get(`${API_URL}/${id}`);
        const p = res.data.data; 

        // 3. Mapping dữ liệu (Sử dụng cấu trúc p.info, p.pricing...)
        document.getElementById('product_id').value = p.id;
        document.getElementById('name').value = p.info.name;
        document.getElementById('sku').value = p.info.sku;
        document.getElementById('description').value = p.info.description || '';

        // Mapping Attributes
        const attrContainer = document.getElementById('attribute-list');
        attrContainer.innerHTML = ''; 
        let specs = p.specifications;
        if (specs && Object.keys(specs).length > 0) {
            Object.entries(specs).forEach(([key, value]) => {
                let strValue = Array.isArray(value) ? value.join(', ') : value;
                window.addAttributeRow(key, strValue);
            });
        }

        // Mapping Category, Price, Stock
        if (p.category) document.getElementById('category_id').value = p.category.id;
        document.getElementById('price').value = p.pricing.original_price;
        document.getElementById('sale_price').value = p.pricing.sale_price;
        document.getElementById('stock_qty').value = p.inventory.stock_qty;
        document.getElementById('is_active').checked = p.is_active;

        // Mapping Images
        document.getElementById('image-preview').src = p.info.thumbnail || '/admin_assets/assets/compiled/jpg/1.jpg';

        // Load Gallery
        if (p.info.images && Array.isArray(p.info.images)) {
            p.info.images.forEach(img => {
                window.renderExistingGalleryItem(img.id, img.url);
            });
        }

        document.getElementById('modalTitle').innerText = 'Cập nhật sản phẩm: ' + p.info.name;

    } catch (error) {
        console.error("Lỗi Edit:", error);
        productModal.hide();
        Swal.fire('Lỗi', 'Không tìm thấy dữ liệu sản phẩm', 'error');
    }
}

// --- 3. LƯU DỮ LIỆU ---

async function saveProduct() {
    console.log("--- BẮT ĐẦU SAVE PRODUCT ---");

    // 1. Thu thập Attributes (Giữ nguyên logic của bạn)
    const attrRows = document.querySelectorAll('.attribute-item');
    const attributes = []; 
    attrRows.forEach((row) => {
        const nameEl = row.querySelector('.attr-name');
        const valueEl = row.querySelector('.attr-value');
        const key = nameEl ? nameEl.value.trim() : ''; 
        const value = valueEl ? valueEl.value.trim() : '';
        if(key && value) {
            attributes.push({ name: key, value: value });
        }
    });

    const id = document.getElementById('product_id').value;
    const form = document.getElementById('productForm');

    // TẠO FORMDATA:
    const formData = new FormData(form);

    // XỬ LÝ QUAN TRỌNG: Kiểm tra ảnh đại diện (Thumbnail)
    // Nếu người dùng KHÔNG chọn file mới, xóa key 'image' rỗng khỏi FormData 
    // để tránh đè dữ liệu cũ trên Backend
    const imageInput = document.getElementById('image');
    if (!imageInput.files || imageInput.files.length === 0) {
        formData.delete('image'); 
    }

    // 2. Thêm Attributes vào FormData
    // Xóa các attributes cũ (nếu có) để append lại cho sạch
    formData.delete('attributes'); 
    attributes.forEach((item, index) => {
        formData.append(`attributes[${index}][name]`, item.name);
        formData.append(`attributes[${index}][value]`, item.value);
    });

    // 3. XỬ LÝ Gallery (Đảm bảo dọn sạch preview sau khi save)
    formData.delete('gallery[]'); 
    selectedGalleryFiles.forEach((fileObj) => {
        const fileData = fileObj.file ? fileObj.file : fileObj; 
        formData.append('gallery[]', fileData);
    });

    // 4. Thêm danh sách ID ảnh cũ cần xóa
    formData.set('deleted_images', JSON.stringify(deletedImageIds));

    // 5. Chuẩn hóa trạng thái is_active
    formData.set('is_active', document.getElementById('is_active').checked ? 1 : 0);

    try {
        let response;
        const config = { headers: { 'Content-Type': 'multipart/form-data' } };

        if (id) {
            formData.append('_method', 'PUT'); 
            response = await window.api.post(`${API_URL}/${id}`, formData, config);
        } else {
            response = await window.api.post(API_URL, formData, config);
        }

        if (response.data.status) {
            if (productModal) productModal.hide();
            Swal.fire({
                icon: 'success',
                title: 'Thành công',
                text: response.data.message,
                timer: 1500,
                showConfirmButton: false
            });
            // 3. QUAN TRỌNG: Gọi hàm Reset Form để làm sạch mọi biến global và UI
            // Điều này giúp lần mở Modal tiếp theo không bị dính ảnh cũ
            if (typeof window.resetForm === 'function') {
                window.resetForm();
            }

            // 4. Reload lại danh sách sản phẩm ở trang chính để cập nhật Thumbnail mới
            if (typeof loadProducts === 'function') {
                loadProducts(); 
            }
        }
    } catch (error) {
        console.error(error);
        const msg = error.response?.data?.message || 'Có lỗi xảy ra';
        Swal.fire('Lỗi', msg, 'error');
    }
}
// --- 4. XÓA SẢN PHẨM ---

function deleteProduct(id) {
    Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: "Dữ liệu sẽ không thể khôi phục!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Xóa ngay',
        cancelButtonText: 'Hủy'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                // SỬA LỖI 401 TẠI ĐÂY: dùng window.api.delete
                await window.api.delete(`${API_URL}/${id}`);
                Swal.fire('Đã xóa!', 'Sản phẩm đã bị xóa.', 'success');
                loadProducts();
            } catch (error) {
                Swal.fire('Lỗi', 'Không thể xóa sản phẩm này', 'error');
            }
        }
    })

// 2. Hàm render ảnh mới (Dùng chung cho đẹp)
function renderNewGalleryItem(url, fileId) {
    const html = `
        <div class="col-4 gallery-item-new" data-file-id="${fileId}">
            <div class="position-relative border border-primary rounded overflow-hidden shadow-sm" style="height: 80px;">
                <img src="${url}" class="w-100 h-100 object-fit-cover" style="opacity: 0.9">
                <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 p-0" 
                        onclick="removeNewSelectedImage(this, '${fileId}')" 
                        style="width: 20px; height: 20px; line-height: 1; border-radius: 0 0 0 5px;">
                    <i class="bi bi-x"></i>
                </button>
                <div class="position-absolute bottom-0 start-0 w-100 bg-primary text-white text-center" style="font-size: 8px;">Mới</div>
            </div>
        </div>`;
    document.getElementById('gallery-preview-container').insertAdjacentHTML('beforeend', html);
}
}

// --- 2. CÁC HÀM ACTION (GÁN VÀO WINDOW ĐỂ HTML GỌI ĐƯỢC) ---
window.openCreateModal = function() {
    const form = document.getElementById('productForm');
    if(form) form.reset();
    
    document.getElementById('attribute-list').innerHTML = '';
    addAttributeRow() // Thêm sẵn 1 dòng trống
    document.getElementById('product_id').value = '';
    document.getElementById('modalTitle').innerText = 'Thêm mới sản phẩm';
    document.getElementById('image-preview').src = '/assets/static/images/no-image.png';
    
    // Reset switch về active
    document.getElementById('is_active').checked = true;

    if(productModal) productModal.show();
}
// --- 1. HÀM THÊM DÒNG ATTRIBUTE (Gắn vào window) ---
window.addAttributeRow = function(key = '', value = '') {
    const container = document.getElementById('attribute-list');
    const rowId = 'attr-row-' + Date.now() + Math.random().toString(36).substr(2, 9);
    
    const row = `
        <tr id="${rowId}" class="attribute-item">
            <td>
                <input type="text" class="form-control form-control-sm attr-name" 
                        list="attribute-suggestions" placeholder="VD: Màu sắc" value="${name}">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm attr-value" 
                        placeholder="VD: Đỏ, Xanh, L, XL" value="${value}">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-light text-danger" onclick="this.closest('tr').remove()">
                    <i class="bi bi-x"></i>
                </button>
            </td>
        </tr>
    `;
    container.insertAdjacentHTML('beforeend', row);
}
// Gắn trực tiếp vào window để HTML luôn tìm thấy
window.handleGallerySelect = function(event) {
    console.log("Đã kích hoạt chọn ảnh gallery");
    const files = Array.from(event.target.files);
    const container = document.getElementById('gallery-preview-container');

    files.forEach(file => {
        const fileId = 'new_' + Date.now() + Math.random().toString(36).substr(2, 9);
        selectedGalleryFiles.push({ id: fileId, file: file });

        const url = URL.createObjectURL(file);
        
        const html = `
            <div class="col-4 gallery-item-new" data-file-id="${fileId}">
                <div class="position-relative border border-primary rounded overflow-hidden shadow-sm" style="height: 80px;">
                    <img src="${url}" class="w-100 h-100 object-fit-cover" style="opacity: 0.9">
                    <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 p-0" 
                            onclick="window.removeNewSelectedImage(this, '${fileId}')" 
                            style="width: 20px; height: 20px; line-height: 1; border-radius: 0 0 0 5px;">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="position-absolute bottom-0 start-0 w-100 bg-primary text-white text-center" style="font-size: 8px;">Mới</div>
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
    });
    event.target.value = ''; 
};

window.removeNewSelectedImage = function(btn, fileId) {
    selectedGalleryFiles = selectedGalleryFiles.filter(item => item.id !== fileId);
    const col = btn.closest('.col-4');
    if (col) col.remove();
};
window.renderExistingGalleryItem = function(id, url) {
    const container = document.getElementById('gallery-preview-container');
    if (!container) return;

    const html = `
        <div class="col-4 gallery-item-old" data-id="${id}">
            <div class="position-relative border rounded overflow-hidden shadow-sm" style="height: 80px;">
                <img src="${url}" class="w-100 h-100 object-fit-cover">
                <button type="button" class="btn btn-danger btn-xs position-absolute top-0 end-0 p-0" 
                        onclick="window.removeExistingImage(this, ${id})" 
                        style="width: 20px; height: 20px; line-height: 1; border-radius: 0 0 0 5px;">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);
};
window.removeExistingImage = function(btn, id) {
    deletedImageIds.push(id);
    btn.closest('.col-4').remove();
}
window.previewMainImage = function(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Thay đổi src của ảnh preview ngay lập tức
            document.getElementById('image-preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
};
// 1. Hàm Reset toàn bộ Form và biến trạng thái
window.resetForm = function() {
    console.log("--- Đang dọn dẹp Form và bộ nhớ tạm ---");
    
    // 1. Reset Form HTML
    const form = document.getElementById('productForm');
    if (form) form.reset();

    // 2. Reset ID ẩn
    document.getElementById('product_id').value = '';

    // 3. Reset các biến Global lưu trữ ảnh (QUAN TRỌNG NHẤT)
    window.selectedGalleryFiles = []; 
    window.deletedImageIds = [];      

    // 4. Dọn dẹp giao diện Gallery & Attributes
    const galleryContainer = document.getElementById('gallery-preview-container');
    if (galleryContainer) galleryContainer.innerHTML = '';

    const attrContainer = document.getElementById('attribute-list');
    if (attrContainer) {
        attrContainer.innerHTML = '';
        // Thêm 1 dòng trống mặc định
        if (typeof window.addAttributeRow === 'function') window.addAttributeRow();
    }

    // 5. Reset ảnh Preview về mặc định
    const preview = document.getElementById('image-preview');
    if (preview) preview.src = '/admin_assets/assets/compiled/jpg/1.jpg';

    // 6. Cập nhật Tiêu đề
    document.getElementById('modalTitle').innerText = 'Thêm mới sản phẩm';
};

window.addNewProduct = function() {
    window.resetForm(); // Dọn dẹp rác từ lần Edit trước đó
    if (productModal) productModal.show();
};
window.editProduct = async function(id) {
    // 1. Dọn dẹp trước khi load để tránh hiện ảnh của sản phẩm cũ trong lúc chờ API
    window.resetForm(); 

    try {
        const res = await window.api.get(`${API_URL}/${id}`);
        const p = res.data.data;

        // 2. Đổ dữ liệu vào (Mapping)
        document.getElementById('modalTitle').innerText = 'Cập nhật sản phẩm: ' + p.info.name;
        document.getElementById('product_id').value = p.id;
        document.getElementById('name').value = p.info.name;
        document.getElementById('sku').value = p.info.sku;
        // ... (các trường khác)

        // 3. Hiển thị ảnh
        document.getElementById('image-preview').src = p.info.thumbnail || '/assets/static/images/no-image.png';
        
        if (p.info.images && Array.isArray(p.info.images)) {
            p.info.images.forEach(img => {
                window.renderExistingGalleryItem(img.id, img.url);
            });
        }

        // 4. Mở modal sau khi đã load xong data
        if(productModal) productModal.show();

    } catch (error) {
        let msg = error.response?.data?.message || "Lỗi không xác định";
        Swal.fire('Lỗi hệ thống', 'Không thể lấy thông tin sản phẩm: ' + msg, 'error');
    }
};
