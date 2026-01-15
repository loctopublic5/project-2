// 1. Cấu hình Chart 
const chartOptions = {
    series: [{ name: 'Doanh thu', data: [] }],
    chart: { height: 350, type: 'area', toolbar: { show: false } },
    colors: ['#435ebe'],
    stroke: { curve: 'smooth' },
    xaxis: { categories: [] },
    dataLabels: { enabled: false },
    tooltip: {
        y: { formatter: (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val) }
    }
};

let revenueChart;

// 2. Hàm format tiền (Giữ nguyên)
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};

// 3. Hàm render giao diện (Đã cập nhật thêm Top Products)
const renderDashboard = (data) => {
    // A. Overview Cards
    if (data.overview) {
        document.getElementById('stat-revenue').innerText = formatCurrency(data.overview.total_revenue);
        document.getElementById('stat-orders').innerText = data.overview.new_orders_today;
        document.getElementById('stat-customers').innerText = data.overview.total_customers;
        document.getElementById('stat-pending').innerText = data.overview.pending_orders;
    }

    // B. Chart Update
    if (data.chart) {
        revenueChart.updateOptions({ xaxis: { categories: data.chart.labels } });
        revenueChart.updateSeries([{ data: data.chart.values }]);
    }

    // C. Table Update: Low Stock (Hàng sắp hết)
    const lowStockBody = document.getElementById('low-stock-list');
    if (lowStockBody && data.low_stock) {
        lowStockBody.innerHTML = '';
        data.low_stock.forEach(item => {
            lowStockBody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${item.name}</td>
                    <td class="text-center"><span class="badge bg-danger">${item.stock_qty}</span></td>
                    <td class="text-end">${formatCurrency(item.price)}</td>
                </tr>
            `);
        });
    }

    // D. Table Update: Top Selling Products (Logic chuẩn theo JSON mới nhất)
    const topProductsBody = document.getElementById('top-products-list');
    
    // Kiểm tra kỹ: data.top_products phải tồn tại và là mảng có dữ liệu
    if (topProductsBody && Array.isArray(data.top_products) && data.top_products.length > 0) {
        
        topProductsBody.innerHTML = ''; // Xóa dữ liệu cũ/loading
        
        data.top_products.forEach(item => {
            // --- 1. Logic tính toán Badge trạng thái ---
            let stockBadge = '';
            let currentStock = parseInt(item.stock_qty); // JSON trả về 414 (int)

            if (currentStock === 0) {
                stockBadge = `<span class="badge bg-danger">Hết hàng</span>`;
            } else if (currentStock <= 10) {
                stockBadge = `<span class="badge bg-warning text-dark">Sắp hết (${currentStock})</span>`;
            } else {
                stockBadge = `<span class="badge bg-success">Còn: ${currentStock}</span>`;
            }

            // --- 2. Render HTML (Đã bỏ Image, Layout cột chuẩn) ---
            // Lưu ý: class 'text-truncate' giúp tên dài không bị vỡ giao diện
            topProductsBody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="col-5">
                        <div class="d-flex align-items-center">
                            <p class="font-bold mb-0 text-truncate" style="max-width: 250px;" title="${item.name}">
                                ${item.name}
                            </p>
                        </div>
                    </td>
                    <td class="col-auto text-center">
                        <p class="mb-0">${item.total_sold}</p>
                    </td>
                    <td class="col-auto text-center">
                        ${stockBadge}
                    </td>
                    <td class="col-auto text-end">
                        <p class="mb-0 font-bold">${formatCurrency(item.total_revenue)}</p>
                    </td>
                </tr>
            `);
        });

    } else if (topProductsBody) {
        // Trường hợp mảng rỗng hoặc null
        topProductsBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Chưa có dữ liệu bán hàng</td></tr>';
    }
};

// 4. Hàm gắn sự kiện Click cho Widget (MỚI THÊM)
const setupWidgetEvents = () => {
    // A. Widget Chờ xử lý -> Trỏ sang đơn hàng + filter pending
    const pendingWidget = document.getElementById('stat-pending');
    if (pendingWidget) {
        pendingWidget.addEventListener('click', () => {
            // Chuyển hướng kèm Query Param
            window.location.href = '/admin/orders?status=pending'; 
        });
    }

    // B. Widget Khách hàng -> Trỏ sang danh sách khách
    const customerWidget = document.getElementById('stat-customers');
    if (customerWidget) {
        customerWidget.addEventListener('click', () => {
            window.location.href = '/admin/users';
        });
    }

    // C. Widget Doanh thu -> Trỏ sang đơn hàng (để xem tất cả)
    const revenueWidget = document.getElementById('widget-revenue'); // Giả sử bạn đã đặt ID này
    if (revenueWidget) {
        revenueWidget.addEventListener('click', () => {
            window.location.href = '/admin/orders';
        });
    }
};

// 5. Hàm Main: Khởi chạy (CẬP NHẬT)
const initDashboard = async (apiUrl) => {
    // Render Chart rỗng
    revenueChart = new ApexCharts(document.querySelector("#revenue-chart"), chartOptions);
    revenueChart.render();

    setupWidgetEvents(); 

    try {
        if (!window.api) throw new Error("Window.api chưa khởi tạo");

        const response = await window.api.get(apiUrl);
        const result = response.data;

        if (result.status || result.success) {
            renderDashboard(result.data);
        }
    } catch (error) {
        console.error("❌ Lỗi tải Dashboard:", error);
    }
};