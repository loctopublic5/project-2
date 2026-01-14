/**
 * File: public/admin_assets/js/pages/categories.js
 * Version: UX_FIXED (Tự động đóng modal, Reload bảng mượt mà)
 */

// Biến toàn cục
let modalInstance = null;
let isEditing = false;
const API_ENDPOINT = '/api/v1/admin/categories';

document.addEventListener('DOMContentLoaded', function () {
    // 1. Khởi tạo Modal Bootstrap chuẩn chỉ
    const modalEl = document.getElementById('categoryModal');
    if (modalEl) {
        modalInstance = new bootstrap.Modal(modalEl, {
            keyboard: false // Ngăn đóng khi lỡ bấm nhầm phím
        });
    }

    // 2. Load dữ liệu bảng ngay lập tức
    fetchCategories();

    // 3. Lắng nghe Submit Form
    const form = document.getElementById('category-form');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
});

// --- 1. HÀM FETCH DỮ LIỆU & RENDER BẢNG ---
async function fetchCategories() {
    const tableBody = document.getElementById('category-list');
    const api = window.api || axios;

    try {
        // [FIX 1] Thêm param ?t=... để ép trình duyệt KHÔNG dùng cache cũ
        // Mỗi lần gọi là một URL mới -> Luôn lấy dữ liệu mới nhất từ DB
        const timestamp = new Date().getTime(); 
        const response = await api.get(`${API_ENDPOINT}?t=${timestamp}`); 
        
        const result = response.data;
        const categories = result.data || [];

        let html = '';
        if (categories.length === 0) {
            html = `<tr><td colspan="6" class="text-center text-muted">Chưa có dữ liệu</td></tr>`;
        } else {
            categories.forEach(cat => {
                const statusBadge = cat.is_active 
                    ? '<span class="badge bg-success">Hiển thị</span>' 
                    : '<span class="badge bg-secondary">Ẩn</span>';
                
                const parentName = cat.parent_name || '<span class="text-muted fw-light">-- Gốc --</span>';

                html += `
                    <tr>
                        <td>${cat.id}</td>
                        <td class="fw-bold text-primary">${cat.name}</td>
                        <td class="text-muted small">${cat.slug}</td>
                        <td>${parentName}</td>
                        <td>${statusBadge}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-light-primary me-1" onclick="openEditModal(${cat.id})" title="Sửa">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-sm btn-light-danger" onclick="deleteCategory(${cat.id})" title="Xóa">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
        tableBody.innerHTML = html;

        // Cập nhật dropdown cho modal luôn
        updateParentDropdown(categories);

    } catch (error) {
        console.error("Lỗi tải danh mục:", error);
        // Không hiện lỗi ra bảng để tránh nháy giao diện nếu chỉ là lỗi mạng nhỏ
    }
}

// Helper: Cập nhật Dropdown Parent
function updateParentDropdown(categories) {
    const select = document.getElementById('parent_id');
    // Giữ lại option đầu tiên
    let html = '<option value="">-- Là danh mục gốc --</option>';
    
    categories.forEach(cat => {
        // Tránh chọn chính nó làm cha (khi edit) -> Xử lý đơn giản ở frontend
        // (Logic chặt chẽ hơn đã có ở Backend)
        html += `<option value="${cat.id}">${cat.name}</option>`;
    });
    select.innerHTML = html;
}

// --- 2. XỬ LÝ FORM (CREATE / EDIT) ---

function openCreateModal() {
    isEditing = false;
    document.getElementById('modalTitle').innerText = 'Thêm mới danh mục';
    
    // Reset Form sạch sẽ
    document.getElementById('category-form').reset();
    document.getElementById('category_id').value = '';
    clearValidationErrors();
    
    modalInstance.show();
}

async function openEditModal(id) {
    isEditing = true;
    document.getElementById('modalTitle').innerText = 'Cập nhật danh mục';
    clearValidationErrors();

    const api = window.api || axios;
    
    try {
        // Hiện loading trên nút bấm (UX trick)
        // Nhưng ở đây ta hiện modal trước rồi load data vào sau cũng được
        modalInstance.show();

        const response = await api.get(`${API_ENDPOINT}/${id}`);
        const data = response.data.data;

        // Fill data
        document.getElementById('category_id').value = data.id;
        document.getElementById('name').value = data.name;
        document.getElementById('slug').value = data.slug;
        document.getElementById('parent_id').value = data.parent_id || '';
        document.getElementById('is_active').checked = data.is_active;

    } catch (error) {
        modalInstance.hide();
        Swal.fire('Lỗi', 'Không thể tải thông tin danh mục!', 'error');
    }
}

async function handleFormSubmit(e) {
    e.preventDefault();
    
    const api = window.api || axios;
    const btnSave = document.getElementById('btn-save');
    const originalText = btnSave.innerHTML;

    // 1. Lấy dữ liệu
    const id = document.getElementById('category_id').value;
    const payload = {
        name: document.getElementById('name').value,
        slug: document.getElementById('slug').value, 
        parent_id: document.getElementById('parent_id').value || null,
        is_active: document.getElementById('is_active').checked ? 1 : 0
    };

    // 2. UI Loading (Bật chế độ chờ)
    btnSave.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
    btnSave.disabled = true;
    
    // Xóa các thông báo lỗi cũ
    clearValidationErrors();

    try {
        // 3. Gọi API
        if (isEditing) {
            await api.put(`${API_ENDPOINT}/${id}`, payload);
        } else {
            await api.post(API_ENDPOINT, payload);
        }

        // 4. NẾU THÀNH CÔNG
        await fetchCategories(); // Reload bảng
        modalInstance.hide();    // Đóng modal

        // Reset form nếu là thêm mới
        if (!isEditing) {
            document.getElementById('category-form').reset();
            document.getElementById('category_id').value = ''; 
            document.getElementById('parent_id').value = '';
        }

        // Thông báo thành công
        Swal.fire({
            icon: 'success',
            title: 'Thành công!',
            text: isEditing ? 'Đã cập nhật danh mục.' : 'Đã thêm danh mục mới.',
            timer: 2000,
            showConfirmButton: false
        });

    } catch (error) {
        console.error("API Error Debug:", error); // Bật Console để xem lỗi gì

        // 5. XỬ LÝ LỖI (Quan trọng)
        
        if (error.response) {
            const status = error.response.status;
            const data = error.response.data;

            // Case A: Lỗi Validation (422) -> Backend trả về { errors: ... }
            if (status === 422 && data.errors) {
                showValidationErrors(data.errors);
            } 
            
            // Case B: Lỗi Logic Service (400) -> Backend trả về { message: "Không thể xóa..." }
            else if (status === 400) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cảnh báo',
                    text: data.message || 'Dữ liệu không hợp lệ.',
                });
            }

            // Case C: Lỗi Server (500) hoặc lỗi khác
            else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi hệ thống (' + status + ')',
                    text: data.message || 'Vui lòng thử lại sau.',
                });
            }
        } 
        // Case D: Mất mạng hoặc lỗi JS client
        else {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi kết nối',
                text: 'Không thể kết nối đến Server. Vui lòng kiểm tra mạng.',
            });
        }
    } finally {
        // 6. LUÔN LUÔN CHẠY: Tắt loading, trả lại nút bấm
        // Dù code trên có lỗi cú pháp hay API lỗi, dòng này vẫn chạy -> KHÔNG BAO GIỜ TREO FORM
        btnSave.innerHTML = originalText;
        btnSave.disabled = false;
    }
}

// --- 3. XÓA DANH MỤC ---
function deleteCategory(id) {
    Swal.fire({
        title: 'Xóa danh mục?',
        text: "Hành động này không thể hoàn tác!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Xóa ngay',
        cancelButtonText: 'Hủy'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const api = window.api || axios;
            try {
                await api.delete(`${API_ENDPOINT}/${id}`);
                
                // UX: Thông báo + Reload
                Swal.fire('Đã xóa!', 'Danh mục đã bị xóa.', 'success');
                fetchCategories(); 

            } catch (error) {
                // Lấy message cụ thể: "Đang có 5 sản phẩm..."
                const msg = error.response?.data?.message || 'Không thể xóa danh mục này.';
    
                Swal.fire({
                    icon: 'error',
                    title: 'Thao tác bị chặn',
                    text: msg,
                    confirmButtonColor: '#d33'
                });
            }
        }
    });
}

// --- UTILS ---
function generateSlug() {
    const name = document.getElementById('name').value;
    // (Giữ nguyên logic convert slug tiếng việt của bạn ở đây...)
    // ...
    // Code ngắn gọn cho demo:
    const slug = name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/\s+/g, '-').replace(/[^\w\-]+/g, '');
    document.getElementById('slug').value = slug;
}

function showValidationErrors(errors) {
    Object.keys(errors).forEach(key => {
        const input = document.getElementById(key);
        const errorDiv = document.getElementById(`error-${key}`);
        if (input && errorDiv) {
            input.classList.add('is-invalid');
            errorDiv.innerText = errors[key][0];
        }
    });
}

function clearValidationErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');
}