/**
 * File: public/admin_assets/js/pages/reset-password.js
 * Logic: Lấy Email từ URL + Token do người dùng nhập tay
 */

document.addEventListener('DOMContentLoaded', function () {
    const resetForm = document.getElementById('reset-form');
    const btnReset = document.getElementById('btn-reset');

    // 1. Tự động điền Email từ URL (do trang trước truyền sang)
    const urlParams = new URLSearchParams(window.location.search);
    const emailFromUrl = urlParams.get('email');

    if (emailFromUrl) {
        document.getElementById('email').value = emailFromUrl;
    } else {
        // Nếu không có email trên URL -> Đá về trang quên pass để nhập lại
        Swal.fire({
            icon: 'warning',
            text: 'Không tìm thấy Email. Vui lòng thực hiện lại.',
            allowOutsideClick: false
        }).then(() => {
            window.location.href = '/forgot-password';
        });
    }

    if (resetForm) {
        resetForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            // Lấy dữ liệu từ Form (Token giờ là input text)
            const email = document.getElementById('email').value;
            const token = document.getElementById('token').value.trim(); // Mã 6 số
            const password = document.getElementById('password').value;
            const password_confirmation = document.getElementById('password_confirmation').value;

            // Validate
            if (!token) {
                Swal.fire({ icon: 'warning', text: 'Vui lòng nhập Mã xác nhận (Token)!' });
                return;
            }
            if (!password || !password_confirmation) {
                Swal.fire({ icon: 'warning', text: 'Vui lòng nhập mật khẩu mới!' });
                return;
            }
            if (password !== password_confirmation) {
                Swal.fire({ icon: 'warning', text: 'Mật khẩu xác nhận không khớp!' });
                return;
            }

            // UI Loading
            const originalBtnText = btnReset.innerHTML;
            btnReset.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang đổi mật khẩu...';
            btnReset.disabled = true;

            try {
                const api = window.api || axios;

                // 2. GỌI API RESET
                // Payload gửi lên: { email, token, password, password_confirmation }
                await api.post('/api/v1/auth/reset-password', {
                    token: token, // User nhập "123456"
                    email: email,
                    password: password,
                    password_confirmation: password_confirmation
                });

                // 3. THÀNH CÔNG
                await Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: 'Mật khẩu đã được thay đổi. Hãy đăng nhập lại bằng mật khẩu mới.',
                    timer: 2000,
                    showConfirmButton: false
                });

                window.location.href = '/login';

            } catch (error) {
                console.error("Reset Password Error:", error);
                
                let msg = "Đổi mật khẩu thất bại.";
                if (error.response && error.response.data) {
                    // Xử lý thông báo lỗi từ Backend (VD: Token sai, Hết hạn...)
                    msg = error.response.data.message || msg;
                }

                Swal.fire({ icon: 'error', title: 'Lỗi', text: msg });
            } finally {
                btnReset.innerHTML = originalBtnText;
                btnReset.disabled = false;
            }
        });
    }
});