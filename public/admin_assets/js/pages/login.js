/**
 * File: public/admin_assets/js/pages/login.js
 * Update: Tích hợp SweetAlert2, Role Redirect và Global API Config
 */

document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const loginBtn = document.getElementById('btn-login');

    // Hàm lấy thông báo lỗi đầu tiên từ object errors (cho trường hợp 422)
    const getFirstError = (errors) => {
        const firstKey = Object.keys(errors)[0];
        if (firstKey && errors[firstKey][0]) {
            return errors[firstKey][0];
        }
        return "Dữ liệu không hợp lệ.";
    };

    if (loginForm) {
        // Lắng nghe sự kiện submit (Hỗ trợ cả Click nút và nhấn Enter)
        loginForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const currentEmail = emailInput.value.trim(); 
            const currentPassword = passwordInput.value.trim();

            console.log("Submit Login:", { email: currentEmail });

            // 1. Validate sơ bộ (Hiển thị Warning Popup)
            if (!currentEmail || !currentPassword) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Thiếu thông tin',
                    text: 'Vui lòng nhập đầy đủ Email và Mật khẩu!',
                    confirmButtonColor: '#435ebe'
                });
                return;
            }

            // UI Loading
            const originalBtnText = loginBtn.innerHTML;
            loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
            loginBtn.disabled = true;

            try {
                // Ưu tiên dùng window.api (nếu đã load axios-config.js), nếu không thì dùng axios thường
                const api = window.api || axios;
                const API_URL = '/api/v1/auth/login';

                // 2. Gọi API Login
                const response = await api.post(API_URL, {
                    email: currentEmail,
                    password: currentPassword,
                });

                const data = response.data;

                // ---------------------------------------------------------
                // CASE: SUCCESS (Xử lý Token và Phân quyền)
                // ---------------------------------------------------------
                let token = null;
                let user = null;

                // Tìm token theo cấu trúc JSON của Backend bạn
                if (data.data && data.data.authorization) {
                    token = data.data.authorization.token;
                    user = data.data.user_info;
                } 
                // Fallback cho các cấu trúc khác (đề phòng backend đổi)
                else {
                    token = data.access_token || (data.data ? data.data.token : null);
                    user = data.user || (data.data ? data.data.user : null);
                }

                if (token) {
                    console.log("Login Success. Token Found.");
                    
                    // a. Lưu Token & User Info vào LocalStorage
                    localStorage.setItem('admin_token', token);
                    if (user) {
                        localStorage.setItem('admin_user', JSON.stringify(user));
                    }

                    // b. Xử lý Redirect theo Role (Phân luồng Admin/Customer)
                    let redirectUrl = '/'; // Mặc định về trang chủ (Customer)
                    let roleName = 'Khách hàng';
                    let userRoles = [];

                    if (user && user.roles) {
                        userRoles = user.roles;
                    }

                    // Logic ưu tiên role
                    if (userRoles.includes('admin') || userRoles.includes('super_admin')) {
                        redirectUrl = '/admin/dashboard';
                        roleName = 'Quản trị viên';
                    } 
                    else if (userRoles.includes('warehouse')) {
                        redirectUrl = '/admin/dashboard'; // Sau này đổi thành layout kho
                        roleName = 'Thủ kho';
                    } 
                    // Nếu là customer -> giữ nguyên redirectUrl = '/'

                    console.log(`User Role: [${userRoles.join(', ')}] -> Redirecting to: ${redirectUrl}`);

                    // c. Hiển thị Popup Thành công
                    await Swal.fire({
                        icon: 'success',
                        title: `Xin chào, ${user.name || roleName}!`,
                        text: 'Đăng nhập thành công. Đang chuyển hướng...',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // d. Chuyển hướng
                    window.location.href = redirectUrl;

                } else {
                    // Trường hợp API trả 200 nhưng không có token trong body
                    console.error("Structure Mismatch:", data);
                    throw new Error("Lỗi hệ thống: Không tìm thấy Token xác thực.");
                }

            } catch (error) {
                console.error("API Error:", error);
                
                let errorMsg = "Đăng nhập thất bại. Vui lòng thử lại.";

                // Xử lý các mã lỗi HTTP cụ thể
                if (error.response) {
                    const status = error.response.status;
                    const resData = error.response.data;

                    // HTTP 422: Lỗi Validation từ Laravel
                    if (status === 422 && resData.errors) {
                        errorMsg = getFirstError(resData.errors);
                    } 
                    // HTTP 401: Sai mật khẩu/tài khoản
                    else if (status === 401) {
                        errorMsg = resData.message || "Email hoặc Mật khẩu không chính xác.";
                    }
                    // HTTP 500: Lỗi Server
                    else if (status === 500) {
                        errorMsg = resData.message || "Lỗi hệ thống (500). Vui lòng liên hệ Admin.";
                    }
                    // Các lỗi khác
                    else {
                        errorMsg = resData.message || `Lỗi hệ thống (${status}).`;
                    }
                } 
                // Lỗi không có response (Mất mạng, Server tắt)
                else if (error.request) {
                    errorMsg = "Không thể kết nối đến Server. Vui lòng kiểm tra mạng.";
                }
                // Lỗi logic code (throw new Error ở trên)
                else {
                    errorMsg = error.message;
                }

                // Hiển thị Popup Lỗi
                Swal.fire({
                    icon: 'error',
                    title: 'Đăng nhập thất bại',
                    text: errorMsg,
                    confirmButtonColor: '#d33'
                });

            } finally {
                // Reset nút bấm về trạng thái ban đầu
                loginBtn.innerHTML = originalBtnText;
                loginBtn.disabled = false;
            }
        });
    }
});