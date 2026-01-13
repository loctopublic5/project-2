/**
 * File: public/admin_assets/js/pages/forgot-password.js
 * Logic: Optimistic UI - Chuyển trang ngay, không chờ kết quả Server
 */

document.addEventListener('DOMContentLoaded', function () {
    const forgotForm = document.getElementById('forgot-form');
    const btnSend = document.getElementById('btn-send');
    const emailInput = document.getElementById('email');

    if (forgotForm) {
        forgotForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const email = emailInput.value.trim();
            // Validate Email đơn giản
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!email || !emailRegex.test(email)) {
                Swal.fire({ icon: 'warning', text: 'Vui lòng nhập đúng định dạng Email!' });
                return;
            }

            // 1. UI Loading (Giả vờ đang xử lý)
            const originalBtnText = btnSend.innerHTML;
            btnSend.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang gửi mã...';
            btnSend.disabled = true;

            const api = window.api || axios;

            // 2. GỌI API (Chạy ngầm, không await kết quả để chặn UI)
            // Backend sẽ tự lo việc gửi mail. Frontend cứ đi tiếp.
            api.post('/api/v1/auth/forgot-password', { email: email })
                .catch(err => console.error("Gửi mail lỗi ngầm:", err));

            // 3. Đợi 1.5 giây cho "giống thật" rồi chuyển trang luôn
            setTimeout(() => {
                // Chuyển sang trang Reset, mang theo email trên URL để đỡ phải nhập lại
                // encodeURIComponent để đảm bảo email có ký tự lạ không bị lỗi URL
                window.location.href = `/admin/reset-password?email=${encodeURIComponent(email)}`;
            }, 1500);
        });
    }
});