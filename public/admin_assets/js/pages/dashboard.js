
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

// 3. Hàm render giao diện (Giữ nguyên)
const renderDashboard = (data) => {
    // Overview Cards
    document.getElementById('stat-revenue').innerText = formatCurrency(data.overview.total_revenue);
    document.getElementById('stat-orders').innerText = data.overview.new_orders_today;
    document.getElementById('stat-customers').innerText = data.overview.total_customers;
    document.getElementById('stat-pending').innerText = data.overview.pending_orders;

    // Chart Update
    revenueChart.updateOptions({ xaxis: { categories: data.chart.labels } });
    revenueChart.updateSeries([{ data: data.chart.values }]);

    // Table Update
    const tableBody = document.getElementById('low-stock-list');
    if (tableBody) {
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
    }
};

// 4. Hàm Main: Khởi chạy (ĐÃ ĐƯỢC LÀM GỌN)
const initDashboard = async (apiUrl) => {
    revenueChart = new ApexCharts(document.querySelector("#revenue-chart"), chartOptions);
    revenueChart.render();

    try {
        // 👇👇👇 QUAN TRỌNG: DÙNG window.api 👇👇👇
        console.log("🚀 Đang gọi API bằng window.api...");
        
        // Kiểm tra xem window.api đã có chưa
        if (!window.api) {
            throw new Error("Lỗi: window.api chưa được khởi tạo. Kiểm tra lại axios-config.js");
        }

        // Gọi API bằng instance đã được cấu hình Token
        const response = await window.api.get(apiUrl);
        
        const result = response.data;
        if (result.status || result.success) {
            renderDashboard(result.data);
        } else {
            console.error("API trả về logic false:", result);
        }

    } catch (error) {
        console.error("❌ Lỗi tải Dashboard:", error);
        // Không cần xử lý 401 ở đây nữa vì window.api đã tự lo rồi
    }
};