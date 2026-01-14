/**
 * File: public/admin_assets/js/pages/register.js
 * Update: Fix Redirect sai Role (Customer bị đá về Admin Dashboard)
 */

document.addEventListener('DOMContentLoaded', function () {
    const registerForm = document.getElementById('register-form');
    const btnRegister = document.getElementById('btn-register');

    // Các field cần validate
    const fields = ['full_name', 'email', 'password', 'password_confirmation'];

    const showValidationErrors = (errors) => {
        Object.keys(errors).forEach(key => {
            const input = document.getElementById(key);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.parentElement.querySelector('.invalid-feedback');
                if (feedback) feedback.textContent = errors[key][0];
            }
        });
    };

    const clearErrors = () => {
        fields.forEach(fieldId => {
            const input = document.getElementById(fieldId);
            if (input) {
                input.classList.remove('is-invalid');
                const feedback = input.parentElement.querySelector('.invalid-feedback');
                if (feedback) feedback.textContent = ''; 
            }
        });
    };

    if (registerForm) {
        registerForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            clearErrors();

            const full_name = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const password_confirmation = document.getElementById('password_confirmation').value;

            // Validate Frontend
            if(!full_name || !email || !password || !password_confirmation) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Thiếu thông tin',
                    text: 'Vui lòng điền đầy đủ các trường bắt buộc!',
                });
                return;
            }

            const originalBtnText = btnRegister.innerHTML;
            btnRegister.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';
            btnRegister.disabled = true;

            try {
                const api = window.api || axios; 

                // 1. GỌI API ĐĂNG KÝ
                await api.post('/api/v1/auth/register', {
                    full_name: full_name,
                    email: email,
                    password: password,
                    password_confirmation: password_confirmation
                });

                // 2. AUTO LOGIN (Để lấy Token và Role)
                const loginResponse = await api.post('/api/v1/auth/login', {
                    email: email,
                    password: password
                });

                const loginData = loginResponse.data;
                let token = null;
                let user = null;

                // Parse dữ liệu trả về từ Login
                if (loginData.data && loginData.data.authorization) {
                    token = loginData.data.authorization.token;
                    user = loginData.data.user_info;
                } else {
                    token = loginData.access_token || loginData.token;
                    user = loginData.user;
                }

                if (token) {
                    // Lưu Token
                    localStorage.setItem('admin_token', token);
                    if (user) localStorage.setItem('admin_user', JSON.stringify(user));

                    // --- [FIX QUAN TRỌNG: CHECK ROLE TRƯỚC KHI REDIRECT] ---
                    
                    let redirectUrl = '/'; // Mặc định về Trang chủ (Shop) cho Customer
                    let userRoles = [];

                    if (user && user.roles) {
                        userRoles = user.roles;
                    }

                    // Chỉ Admin/Warehouse mới về Dashboard
                    if (userRoles.includes('admin') || userRoles.includes('super_admin')) {
                        redirectUrl = '/admin/dashboard';
                    } else if (userRoles.includes('warehouse')) {
                        redirectUrl = '/admin/dashboard';
                    }
                    
                    // Nếu là customer (role rỗng hoặc role='customer') -> Vẫn giữ là '/'

                    console.log(`Registered as [${userRoles}] -> Redirecting to: ${redirectUrl}`);

                    await Swal.fire({
                        icon: 'success',
                        title: 'Đăng ký thành công!',
                        text: 'Đang đăng nhập vào hệ thống...',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // Chuyển hướng đúng chỗ
                    window.location.href = redirectUrl;
                }

            } catch (error) {
                console.error("Register Error:", error);
                
                let errorMessage = "Đăng ký thất bại.";
                
                if (error.response) {
                    const status = error.response.status;
                    const data = error.response.data;

                    if (status === 422 && data.errors) {
                        showValidationErrors(data.errors);
                        errorMessage = "Thông tin nhập vào chưa chính xác.";
                    } else {
                        errorMessage = data.message || errorMessage;
                    }
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: errorMessage,
                });

            } finally {
                btnRegister.innerHTML = originalBtnText;
                btnRegister.disabled = false;
            }
        });
    }
});