const API_URL = '/api/v1/admin/orders';
let orderModal;
let currentOrderId = null; // Lưu ID đơn hàng đang xem để thao tác

// Dictionary: Màu sắc trạng thái
const STATUS_COLORS = {
    'pending': { color: 'warning', label: 'Chờ duyệt', icon: 'bi-hourglass-split' },
    'confirmed': { color: 'info', label: 'Đã duyệt', icon: 'bi-check-circle' },
    'shipping': { color: 'primary', label: 'Đang giao', icon: 'bi-truck' },
    'completed': { color: 'success', label: 'Hoàn thành', icon: 'bi-check-all' },
    'cancelled': { color: 'danger', label: 'Đã hủy', icon: 'bi-x-circle' },
    'failed': { color: 'secondary', label: 'Giao thất bại', icon: 'bi-exclamation-octagon' }
};

document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('orderDetailModal');
    if(modalEl) orderModal = new bootstrap.Modal(modalEl);
    
    loadOrders();
});

// Helper: Format tiền
const formatMoney = (amount) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
const formatDate = (dateStr) => new Date(dateStr).toLocaleString('vi-VN');

// --- 1. FILTER & LOAD DATA ---

let currentStatusFilter = '';

window.filterStatus = function(status) {
    currentStatusFilter = status;
    // Update Active Tab UI
    document.querySelectorAll('#orderStatusTabs .nav-link').forEach(el => el.classList.remove('active'));
    document.querySelector(`#orderStatusTabs .nav-link[data-status="${status}"]`).classList.add('active');
    
    loadOrders(1);
}

async function loadOrders(page = 1) {
    const tbody = document.getElementById('order-list-body');
    const keyword = document.getElementById('search-input').value;
    
    // Skeleton loading
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>';

    try {
        const params = { page, keyword, status: currentStatusFilter };
        const res = await window.api.get(API_URL, { params });
        
        // --- FIX LOGIC LẤY DATA & META ---
        // API Response: { status: true, data: { data: [...], meta: {...}, links: {...} } }
        const responsePayload = res.data.data; 
        
        // Kiểm tra xem payload là mảng hay object (do Resource Collection trả về)
        let orders = [];
        let meta = null;

        if (Array.isArray(responsePayload)) {
            // Trường hợp ít gặp: API trả về mảng trực tiếp
            orders = responsePayload;
        } else {
            // Trường hợp chuẩn: Resource Collection trả về object chứa data và meta
            orders = responsePayload.data; 
            meta = responsePayload.meta; 
        }
        // ----------------------------------

        tbody.innerHTML = '';
        if (!orders || orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Không tìm thấy đơn hàng nào.</td></tr>';
            renderPagination({ total: 0 }); // Xóa phân trang
            return;
        }

        orders.forEach(order => {
            const st = order.status; 
            const customerName = order.customer ? order.customer.full_name : 'Khách vãng lai';

            const row = `
                <tr class="${st.key === 'pending' ? 'table-warning fw-bold' : ''}">
                    <td><a href="#" class="fw-bold text-decoration-none" onclick="viewOrder(${order.id}); return false;">${order.code}</a></td>
                    <td>
                        <div class="fw-bold" style="max-width: 200px;">${customerName}</div>
                        <small class="text-muted">${order.customer?.phone || ''}</small>
                    </td>
                    <td>${order.created_at}</td>
                    <td class="text-primary fw-bold">${formatMoney(order.total_amount)}</td>
                    <td><span class="badge border text-body bg-transparent">${order.payment_method.toUpperCase()}</span></td>
                    <td><span class="badge bg-${st.color}">${st.label}</span></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-light-primary" onclick="viewOrder(${order.id})">
                            <i class="bi bi-eye-fill"></i> Xem
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        // GỌI HÀM PHÂN TRANG (Giờ meta đã có dữ liệu đúng)
        if (meta) {
            renderPagination(meta);
        }

    } catch (error) {
        console.error("Load Error", error);
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Lỗi kết nối Server!</td></tr>';
    }
}

// --- 2. PAGINATION LOGIC (NEW) ---
function renderPagination(meta) {
    const paginationUl = document.getElementById('pagination-links');
    const infoDiv = document.getElementById('pagination-info');
    
    if (!paginationUl) return;

    // 1. Hiển thị thông tin: "Hiển thị 1-20 / 100"
    if (infoDiv && meta.total > 0) {
        infoDiv.innerHTML = `Hiển thị <b>${meta.from || 0}</b> - <b>${meta.to || 0}</b> trong tổng số <b>${meta.total}</b> đơn hàng`;
    }

    // 2. Nếu chỉ có 1 trang -> Ẩn nút bấm
    if (!meta.last_page || meta.last_page <= 1) {
        paginationUl.innerHTML = '';
        return;
    }

    let html = '';
    
    // Nút Previous
    const prevDisabled = meta.current_page === 1 ? 'disabled' : '';
    html += `<li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadOrders(${meta.current_page - 1})">
                    <i class="bi bi-chevron-left"></i>
                </a>
             </li>`;

    // Logic rút gọn trang (Ví dụ: 1 ... 4 5 6 ... 10)
    const current = meta.current_page;
    const last = meta.last_page;
    const delta = 1; // Số trang hiện bên cạnh trang hiện tại
    
    for (let i = 1; i <= last; i++) {
        // Hiển thị trang đầu, trang cuối, và các trang xung quanh trang hiện tại
        if (i === 1 || i === last || (i >= current - delta && i <= current + delta)) {
            const active = i === current ? 'active' : '';
            html += `<li class="page-item ${active}">
                        <a class="page-link" href="#" onclick="event.preventDefault(); loadOrders(${i})">${i}</a>
                     </li>`;
        } 
        // Hiển thị dấu ...
        else if (i === current - delta - 1 || i === current + delta + 1) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    // Nút Next
    const nextDisabled = meta.current_page === last ? 'disabled' : '';
    html += `<li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" onclick="event.preventDefault(); loadOrders(${meta.current_page + 1})">
                    <i class="bi bi-chevron-right"></i>
                </a>
             </li>`;

    paginationUl.innerHTML = html;
}

// --- 2. VIEW DETAILS & ACTION CENTER ---

window.viewOrder = async function(id) {
    try {
        const res = await window.api.get(`${API_URL}/${id}`);
        const order = res.data.data;
        currentOrderId = order.id;

        // 1. Basic Info
        document.getElementById('modal-order-code').innerText = order.code;
        document.getElementById('modal-customer-name').innerText = order.customer?.full_name || 'N/A';
        document.getElementById('modal-customer-phone').innerText = order.customer?.phone || 'N/A';
        // Resource trả về shipping_address object hoặc string, nếu object thì lấy .address_detail
        document.getElementById('modal-shipping-address').innerText = order.shipping_address?.address_detail || order.shipping_address || 'N/A';
        
        document.getElementById('modal-payment-method').innerText = order.payment_method.toUpperCase();
        
        // 2. Items
        const itemsBody = document.getElementById('modal-order-items');
        itemsBody.innerHTML = '';
        
        if (order.items && order.items.length > 0) {
            order.items.forEach(item => {
                // Giả sử OrderItemResource có trả về 'product_name', 'price', 'quantity'
                // Và nested 'product' -> 'image' -> 'url'
                // Bạn cần check lại file OrderItemResource của bạn.
                // Nếu OrderItemResource chỉ trả về các field phẳng, thì dùng item.product_thumbnail
                
                // Fallback ảnh an toàn
                let img = '/assets/static/images/no-image.png';
                if(item.product && item.product.image) img = item.product.image; // Tùy cấu trúc resource item

                itemsBody.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${img}" width="40" height="40" class="rounded me-2 border">
                                <div>
                                    <div class="small fw-bold">${item.product_name || 'Sản phẩm'}</div>
                                    <div class="text-muted small" style="font-size: 0.8em">SKU: ${item.sku || '---'}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${formatMoney(item.price)}</td>
                        <td class="text-end fw-bold">${formatMoney(item.price * item.quantity)}</td>
                    </tr>
                `);
            });
        }

        // 3. Totals
        document.getElementById('modal-subtotal').innerText = formatMoney(order.total_amount - order.shipping_fee);
        document.getElementById('modal-shipping').innerText = formatMoney(order.shipping_fee);
        document.getElementById('modal-total').innerText = formatMoney(order.total_amount);
        document.getElementById('orderDetailModal').removeAttribute('tabindex');

        // 4. Cancel Reason (Lấy từ note nếu status là cancelled)
        const reasonArea = document.getElementById('cancel-reason-area');
        if (order.status.key === 'cancelled') {
    // Ưu tiên lấy cancel_reason, nếu không có (đơn cũ) thì lấy note, hoặc báo N/A
    const reasonText = order.cancel_reason || order.note || 'Không có lý do cụ thể';
    
    document.getElementById('cancel-reason-text').innerText = reasonText;
    reasonArea.classList.remove('d-none');
} else {
    reasonArea.classList.add('d-none');
}

        // 5. Actions (QUAN TRỌNG: Truyền key 'pending', 'confirmed'...)
        renderActions(order.status.key);

        orderModal.show();

    } catch (error) {
        console.error(error);
        Swal.fire('Lỗi', 'Không thể tải chi tiết đơn hàng', 'error');
    }
}

// Hàm quyết định nút nào được hiện
function renderActions(status) {
    const container = document.getElementById('modal-actions');
    container.innerHTML = ''; // Reset

    let buttons = '';

    switch (status) {
        case 'pending':
            buttons = `
                <button class="btn btn-danger me-2" onclick="updateOrderStatus('cancelled')">
                    <i class="bi bi-x-circle"></i> Hủy đơn
                </button>
                <button class="btn btn-info text-white" onclick="updateOrderStatus('confirmed')">
                    <i class="bi bi-check-lg"></i> Duyệt đơn
                </button>
            `;
            break;
        case 'confirmed':
            buttons = `
                <button class="btn btn-primary" onclick="updateOrderStatus('shipping')">
                    <i class="bi bi-truck"></i> Giao hàng
                </button>
            `;
            break;
        case 'shipping':
            buttons = `
                <button class="btn btn-secondary me-2" onclick="updateOrderStatus('failed')">
                    <i class="bi bi-exclamation-triangle"></i> Giao thất bại
                </button>
                <button class="btn btn-success" onclick="updateOrderStatus('completed')">
                    <i class="bi bi-check-all"></i> Hoàn thành
                </button>
            `;
            break;
        default:
            // Completed hoặc Cancelled thì không còn action
            buttons = `<span class="text-muted fst-italic">Đơn hàng đã đóng</span>`;
    }

    container.innerHTML = buttons;
}

// --- 3. HANDLE UPDATE STATUS (API CALL) ---

window.updateOrderStatus = async function(newStatus) {
    if (!currentOrderId) return;

    let reason = null;

    // Case 1: Cần nhập lý do (Hủy hoặc Giao thất bại)
    if (newStatus === 'cancelled' || newStatus === 'failed') {
        
        // Sử dụng cấu trúc chuẩn của SweetAlert2 để đảm bảo input hoạt động
        const result = await Swal.fire({
            title: newStatus === 'cancelled' ? 'Xác nhận Hủy đơn?' : 'Xác nhận Giao thất bại?',
            text: "Hành động này không thể hoàn tác!",
            icon: 'warning',
            input: 'textarea', // KHAI BÁO INPUT TEXTAREA
            target: document.getElementById('orderDetailModal'),
            inputLabel: 'Lý do (Bắt buộc)',
            inputPlaceholder: 'Nhập lý do tại đây...',
            inputAttributes: {
                'aria-label': 'Nhập lý do'
            },
            validationMessage: 'Bạn bắt buộc phải nhập lý do!', // Message mặc định nếu rỗng
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Xác nhận',
            cancelButtonText: 'Quay lại',
            
            // Validator: Chặn nếu để trống
            inputValidator: (value) => {
                if (!value) {
                    return 'Vui lòng nhập lý do!';
                }
            }
        });

        // Nếu người dùng bấm "Quay lại" hoặc click ra ngoài
        if (!result.isConfirmed) return;

        // Lấy giá trị input
        reason = result.value;
    } 
    
    // Case 2: Các trạng thái khác (Chỉ cần confirm Yes/No)
    else {
        const result = await Swal.fire({
            title: 'Xác nhận thay đổi?',
            text: `Bạn muốn chuyển trạng thái sang: ${newStatus}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        });

        if (!result.isConfirmed) return;
    }

    // Gửi API
    try {
        Swal.fire({
            title: 'Đang xử lý...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const payload = { status: newStatus };
        if (reason) payload.reason = reason;

        await window.api.patch(`${API_URL}/${currentOrderId}/status`, payload);

        // Thành công
        Swal.fire({
            icon: 'success',
            title: 'Thành công!',
            text: 'Trạng thái đơn hàng đã được cập nhật.',
            timer: 1500,
            showConfirmButton: false
        });
        
        if(orderModal) orderModal.hide();
        loadOrders(); // Reload lại bảng và phân trang

    } catch (error) {
        // Xử lý lỗi
        console.error(error);
        const msg = error.response?.data?.message || 'Có lỗi xảy ra';
        
        if (error.response?.status === 422) {
            Swal.fire('Thao tác không hợp lệ', msg, 'warning');
        } else {
            Swal.fire('Lỗi', msg, 'error');
        }
    }
}

// Xử lý lỗi đặc thù (422, 400)
function handleApiError(error) {
    const status = error.response ? error.response.status : 500;
    const data = error.response ? error.response.data : {};

    if (status === 422) {
        // Lỗi State Machine (VD: Đang shipping mà đòi quay lại pending)
        Swal.fire({
            title: 'Sai luồng xử lý!',
            text: data.message || 'Trạng thái không hợp lệ. Đơn hàng có thể đã được cập nhật bởi người khác.',
            icon: 'warning'
        }).then(() => {
            orderModal.hide();
            loadOrders(); // Reload để user thấy trạng thái mới nhất
        });
    } else if (status === 400 || status === 403) {
        // Lỗi Business (Hết hàng trong kho, Lỗi trừ tiền ví)
        Swal.fire({
            title: 'Không thể cập nhật',
            text: data.message || 'Lỗi nghiệp vụ (Kho/Ví).',
            icon: 'error'
        });
    } else {
        Swal.fire('Lỗi hệ thống', 'Vui lòng thử lại sau.', 'error');
    }
}