const AppAccount = {
    // 1. Khởi tạo Dashboard
    init: async function() {
        await this.loadDashboardData();

        this.bindEvents();
    },

    // 2. Load dữ liệu tổng hợp
    loadDashboardData: async function() {
        try {
            const [walletRes, ordersRes] = await Promise.all([
                window.api.get('/api/v1/customer/wallet'),
                window.api.get('/api/v1/customer/orders?per_page=10')
            ]);

            if (walletRes.data.status && ordersRes.data.status) {
                const wallet = walletRes.data.data;
                const orders = ordersRes.data.data;

                // Cập nhật số dư
                document.getElementById('db-balance').innerText = AppHelpers.formatCurrency(parseFloat(wallet.balance));
                
                // Logic Count Đơn hàng đang chờ
                const pendingStatusKeys = ['pending', 'confirmed', 'shipping'];
                const pendingCount = orders.filter(order => 
                    pendingStatusKeys.includes(order.status.key)
                ).length;

                document.getElementById('db-pending').innerText = pendingCount;

                // Render bảng
                OrderModule.renderRecentOrders(orders);
            }
        } catch (err) {
            console.error("Dashboard Load Error:", err);
            // Áp dụng SweetAlert cho lỗi tải dữ liệu
            Swal.fire({
                icon: 'error',
                title: 'Lỗi tải dữ liệu',
                text: 'Không thể kết nối với máy chủ. Vui lòng thử lại sau.',
                confirmButtonColor: '#E02222'
            });
        }
    },

    bindEvents: function() {
        $('.sidebar-menu li').on('click', function() {
            const tab = $(this).data('tab');
            AppAccount.switchTab(tab);
        });
    },

    switchTab: function(tabName) {
    // 1. Xử lý Menu bên trái
    $('.sidebar-menu li').removeClass('active');
    $(`.sidebar-menu li[data-tab="${tabName}"]`).addClass('active');

    // 2. Xử lý nội dung bên phải (Ẩn tất cả, hiện cái được chọn)
    $('.account-tab-content').hide(); // Ẩn toàn bộ các div nội dung
    $(`#tab-${tabName}`).fadeIn();    // Hiện tab tương ứng với hiệu ứng mượt

    // 3. Gọi hàm tải dữ liệu của Module tương ứng
    if (tabName === 'dashboard') {
        this.loadDashboardData();
    } else if (tabName === 'wallet') {
        WalletModule.init(); // Đảm bảo WalletModule đã nạp
    } else if (tabName === 'orders') {
        OrderModule.init();  // Đảm bảo OrderModule đã nạp
    }
},

    // 5. Logout với SweetAlert2
    logout: function() {
        Swal.fire({
            title: 'Xác nhận đăng xuất?',
            text: "Bạn sẽ cần đăng nhập lại để truy cập thông tin cá nhân.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#E02222',
            cancelButtonColor: '#777',
            confirmButtonText: 'Đồng ý, đăng xuất!',
            cancelButtonText: 'Hủy',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Đang xử lý...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const performClientLogout = () => {
                    localStorage.removeItem('token'); 
                    if (window.api && window.api.defaults) {
                        delete window.api.defaults.headers.common['Authorization'];
                    }
                    window.location.href = '/login'; 
                };

                if (window.api) {
                    window.api.post('/api/v1/customer/logout')
                        .then(() => performClientLogout())
                        .catch(() => performClientLogout());
                } else {
                    performClientLogout();
                }
            }
        });
    },

}