/**
 * File: public/admin_assets/js/pages/login.js
 * Update: Handle HTTP 500 with custom message
 */

document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const loginBtn = document.getElementById('btn-login');
    const errorAlert = document.getElementById('error-alert');

    const API_URL = '/api/v1/auth/login';

    const showError = (message) => {
        if(errorAlert) {
            errorAlert.classList.remove('d-none');
            errorAlert.innerHTML = message;
        } else {
            alert(message);
        }
    };

    const hideError = () => {
        if(errorAlert) errorAlert.classList.add('d-none');
    };

    const getFirstError = (errors) => {
        const firstKey = Object.keys(errors)[0];
        if (firstKey && errors[firstKey][0]) {
            return errors[firstKey][0];
        }
        return "Dữ liệu không hợp lệ.";
    };

    if (loginForm) {
        loginForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            hideError();

            const currentEmail = emailInput.value.trim(); 
            const currentPassword = passwordInput.value.trim();

            console.log("Submit Login:", { email: currentEmail });

            if (!currentEmail || !currentPassword) {
                showError("Vui lòng nhập đầy đủ Email và Mật khẩu!");
                return;
            }

            const originalBtnText = loginBtn.innerHTML;
            loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
            loginBtn.disabled = true;

            try {
                const response = await axios.post(API_URL, {
                    email: currentEmail,
                    password: currentPassword,
                });

                const data = response.data; // Đây là toàn bộ cục JSON bạn dán ở trên

                // CASE 1: HTTP 200 - Logical Error (status: false)
                if (data.status === false) {
                    if (data.errors) {
                        showError(getFirstError(data.errors));
                    } else {
                        showError(data.message || "Đăng nhập thất bại.");
                    }
                    return; 
                }

                // ---------------------------------------------------------
                // CASE 2: SUCCESS - [FIX QUAN TRỌNG TẠI ĐÂY]
                // ---------------------------------------------------------
                // Chúng ta phải tìm token theo đúng cấu trúc JSON của bạn:
                // data.data.authorization.token
                
                let token = null;
                let user = null;

                // Cách 1: Cấu trúc chuẩn bạn vừa gửi
                if (data.data && data.data.authorization) {
                    token = data.data.authorization.token;
                    user = data.data.user_info;
                }
                // Cách 2: Fallback (Dự phòng cho các cấu trúc cũ/khác)
                else {
                    token = data.access_token || data.token || (data.data ? data.data.token : null);
                    user = data.user || (data.data ? data.data.user : null);
                }

                if (token) {
                    console.log("Login Success. Token Found:", token);
                    
                    // 1. Lưu Token & User Info
                    localStorage.setItem('admin_token', token);
                    
                    if (user) {
                        localStorage.setItem('admin_user', JSON.stringify(user));
                    }

                    // 2. XỬ LÝ REDIRECT THEO ROLE (Update Mới)
                    let redirectUrl = '/admin/dashboard'; // Mặc định về Dashboard Admin
                    let userRoles = [];

                    // Lấy mảng roles từ object user
                    if (user && user.roles) {
                        userRoles = user.roles;
                    }

                    console.log("User Roles:", userRoles);

                    // Logic ưu tiên: Admin -> Warehouse -> Customer
                    if (userRoles.includes('admin')) {
                        redirectUrl = '/admin/dashboard';
                    } 
                    else if (userRoles.includes('warehouse')) {
                        // Tạm thời vẫn trỏ về admin dashboard vì bạn chưa làm view warehouse
                        // Sau này đổi thành: '/warehouse/dashboard'
                        redirectUrl = '/admin/dashboard'; 
                    } 
                    else if (userRoles.includes('customer')) {
                        // Khách hàng thì về trang chủ hoặc trang cá nhân
                        redirectUrl = '/'; 
                    }

                    // Thực hiện chuyển hướng
                    window.location.href = redirectUrl;

                } else {
                    console.error("Structure Mismatch:", data);
                    showError("Lỗi hệ thống: Không tìm thấy Token xác thực.");
                }

            } catch (error) {
                console.error("API Error:", error);

                if (error.response) {
                    const status = error.response.status;
                    const resData = error.response.data;

                    // CASE 3: HTTP 422 (Validation)
                    if (status === 422 && resData.errors) {
                        showError(getFirstError(resData.errors));
                    } 
                    // CASE 4: HTTP 401 (Unauthorized)
                    else if (status === 401) {
                        showError(resData.message || "Thông tin đăng nhập không đúng.");
                    }
                    // CASE 5: HTTP 500 (Internal Server Error) - CẬP NHẬT MỚI
                    // Nếu Backend trả về 500 kèm message cụ thể, ưu tiên hiển thị message đó
                    else if (status === 500) {
                        showError(resData.message || "Lỗi hệ thống (500). Vui lòng liên hệ Admin.");
                    }
                    // Các lỗi khác
                    else {
                        showError(resData.message || `Lỗi hệ thống (${status}). Vui lòng thử lại.`);
                    }
                } else {
                    showError("Không thể kết nối đến Server. Vui lòng kiểm tra mạng.");
                }
            } finally {
                loginBtn.innerHTML = originalBtnText;
                loginBtn.disabled = false;
            }
        });
    }
});