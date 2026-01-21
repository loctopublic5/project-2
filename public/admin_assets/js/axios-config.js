/**
 * File: public/admin_assets/js/axios-config.js
 * Nhiệm vụ: Tạo ra window.api - Một phiên bản Axios đã được độ chế (gắn Token)
 */

(function () {
    // Kiểm tra thư viện gốc
    if (!window.axios) {
        console.error("❌ Lỗi: Thư viện Axios chưa được nạp!");
        return;
    }

    console.log("⚙️ Đang khởi tạo window.api...");

    // 1. TẠO INSTANCE RIÊNG (Không dùng chung với mặc định để tránh xung đột)
    const api = window.axios.create({
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    // 2. CẤU HÌNH REQUEST (Gửi đi)
    api.interceptors.request.use(
        (config) => {
            const token = localStorage.getItem('admin_token');
            if (token) {
                // [DEBUG] In ra để chắc chắn nó chạy
                console.log(`🎫 Interceptor: Đính Token [${token.substring(0, 10)}...]`);
                config.headers.Authorization = `Bearer ${token}`;
            } else {
                console.warn("⚠️ Interceptor: Không tìm thấy Token!");
            }
            return config;
        },
        (error) => Promise.reject(error)
    );

    // 3. CẤU HÌNH RESPONSE (Nhận về)
    api.interceptors.response.use(
        (response) => response,
        (error) => {
            // Xử lý lỗi 401 (Hết hạn)
            if (error.response && error.response.status === 401) {
                console.error("⛔ Lỗi 401: Token hết hạn hoặc không hợp lệ.");
                
                // Chỉ redirect nếu không phải đang ở trang login
                if (!window.location.pathname.includes('/admin/login')) {
                    alert("Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.");
                    localStorage.removeItem('admin_token');
                    localStorage.removeItem('admin_user');
                    window.location.href = '/login';
                }
            }
            return Promise.reject(error);
        }
    );

    // 4. GẮN VÀO WINDOW ĐỂ DÙNG TOÀN CẦU
    window.api = api;
    console.log("✅ window.api đã sẵn sàng sử dụng!");
})();