/**
 * File: public/admin_assets/js/pages/dashboard.js
 */

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

// 2. Hàm format tiền
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
};

// 3. Hàm render giao diện sau khi có dữ liệu
const renderDashboard = (data) => {
    // Ẩn spinner, hiện số liệu
    // Overview
    document.getElementById('stat-revenue').innerText = formatCurrency(data.overview.total_revenue);
    document.getElementById('stat-orders').innerText = data.overview.new_orders_today;
    document.getElementById('stat-customers').innerText = data.overview.total_customers;
    document.getElementById('stat-pending').innerText = data.overview.pending_orders;

    // Chart
    revenueChart.updateOptions({ xaxis: { categories: data.chart.labels } });
    revenueChart.updateSeries([{ data: data.chart.values }]);

    // Table Low Stock
    const tableBody = document.getElementById('low-stock-list');
    tableBody.innerHTML = '';
    data.low_stock.forEach(item => {
        tableBody.insertAdjacentHTML('beforeend', `
            <tr>
                <td>${item.name}</td>
                <td class="text-center"><span class="badge bg-danger">${item.stock_qty}</span></td>
                <td class="text-end">${formatCurrency(item.price)}</td>
            </tr>
        `);
    });
};

// 4. Hàm Main: Khởi chạy
const initDashboard = async (apiUrl) => {
    // 1. Init Chart
    revenueChart = new ApexCharts(document.querySelector("#revenue-chart"), chartOptions);
    revenueChart.render();

    // 2. Lấy CSRF Token từ thẻ meta (QUAN TRỌNG)
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const response = await axios.get(apiUrl, {
            withCredentials: true, // Cho phép gửi Cookie Session
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken // <--- GỬI KÈM CHÌA KHÓA BẢO MẬT
            }
        });
        
        // ... (Đoạn xử lý data giữ nguyên) ...
        const result = response.data;
        if (result.status || result.success) {
            renderDashboard(result.data); // hoặc result.data.data
        }

    } catch (error) {
        console.error("Lỗi:", error);
        if (error.response && error.response.status === 401) {
            alert("Vui lòng đăng nhập lại!");
            // window.location.reload();
        }
    }
};