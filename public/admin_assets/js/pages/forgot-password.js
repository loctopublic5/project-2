/**
 * File: public/admin_assets/js/pages/forgot-password.js
 * Update: Kiểm tra Email tồn tại thật sự rồi mới chuyển trang (UX Friendly)
 */

document.addEventListener('DOMContentLoaded', function () {
    const forgotForm = document.getElementById('forgot-form');
    const btnSend = document.getElementById('btn-send');
    const emailInput = document.getElementById('email');

    if (forgotForm) {
        forgotForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const email = emailInput.value.trim();

            if (!email) {
                Swal.fire({ icon: 'warning', text: 'Vui lòng nhập Email!' });
                return;
            }

            // 1. Loading thật (chờ Server phản hồi)
            const originalBtnText = btnSend.innerHTML;
            btnSend.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang kiểm tra & gửi mail...';
            btnSend.disabled = true;

            try {
                const api = window.api || axios;

                // 2. GỌI API VÀ CHỜ KẾT QUẢ (AWAIT)
                // Nếu email sai, Backend sẽ trả về lỗi (404 hoặc 422) -> Nhảy xuống Catch
                const response = await api.post('/api/v1/auth/forgot-password', { email: email });

                // 3. NẾU THÀNH CÔNG (Code chạy đến đây nghĩa là Email đúng)
                console.log("Email tồn tại, đã gửi mã.");

                // Thông báo nhẹ
                const Toast = Swal.mixin({
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000
                });
                Toast.fire({ icon: 'success', title: 'Đã gửi mã xác nhận!' });

                // Chuyển trang ngay lập tức
                window.location.href = `/reset-password?email=${encodeURIComponent(email)}`;

            } catch (error) {
                console.error("Forgot Password Error:", error);
                
                let msg = "Có lỗi xảy ra.";
                
                // Xử lý lỗi cụ thể để báo cho User biết họ sai ở đâu
                if (error.response) {
                    const status = error.response.status;
                    const data = error.response.data;

                    if (status === 404 || status === 422) {
                        // Đây là cái bạn cần: Báo lỗi nếu email không khớp
                        msg = "Email này không tồn tại trong hệ thống!";
                    } else if (data.message) {
                        msg = data.message;
                    }
                }

                // Hiện lỗi và GIỮ NGUYÊN TRANG để user nhập lại
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi nhập liệu',
                    text: msg,
                    confirmButtonText: 'Thử lại'
                });

            } finally {
                // Tắt loading
                btnSend.innerHTML = originalBtnText;
                btnSend.disabled = false;
            }
        });
    }
});