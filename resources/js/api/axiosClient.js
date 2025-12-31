import axios from 'axios';

const axiosClient = axios.create({
    baseURL: '/api', // Base URL cho API Laravel
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// 1. Interceptor Request: Tự động gắn Token
axiosClient.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('access_token');
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
        // Trả về data trực tiếp để code gọn hơn
        return response.data;
    },
    (error) => {
        const { response } = error;
        
        if (response) {
            // Xử lý lỗi 401 (Unauthorized) -> Logout & Redirect
            if (response.status === 401) {
                localStorage.removeItem('access_token');
                // Chỉ redirect nếu không phải đang ở trang login để tránh loop
                if (!window.location.pathname.includes('/login')) {
                    window.location.href = '/login';
                }
            }
        }
        
        throw error;
    }
);

export default axiosClient;