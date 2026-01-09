import axios from "axios";

const axiosClient = axios.create({
    baseURL: "http://127.0.0.1:8000/api/v1",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
});

// 1. Interceptor Request: Tự động gắn Token
axiosClient.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem("access_token");
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// 2. Interceptor Response: Xử lý Data & Lỗi Global
axiosClient.interceptors.response.use(
    (response) => {
        /**
         * Theo quy chuẩn dự án, trả về response.data trực tiếp
         * để ở ngoài app.js bạn có thể lấy dữ liệu ngay lập tức.
         */
        return response.data;
    },
    (error) => {
        const { response } = error;

        if (response) {
            // Xử lý lỗi 401 (Unauthorized): Token sai hoặc hết hạn
            if (response.status === 401) {
                localStorage.removeItem("access_token");

                // Tránh lặp vô hạn nếu đang ở trang login
                if (!window.location.pathname.includes("/login")) {
                    window.location.href = "/login";
                }
            }

            // Log lỗi để dễ debug trong quá trình phát triển (Bad Case)
            console.error(
                `[API Error] ${response.status}:`,
                response.data.message || "Lỗi hệ thống"
            );
        }

        return Promise.reject(error);
    }
);

export default axiosClient;
