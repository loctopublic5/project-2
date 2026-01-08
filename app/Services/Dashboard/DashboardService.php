<?php

namespace App\Services\Dashboard;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Lấy toàn bộ số liệu Dashboard (Có Caching)
     * Cache Time: 5 phút (300 giây)
     */
    public function getDashboardData()
    {
        // Key cache cố định (Sau này có filter date thì nối thêm chuỗi vào key)
        return Cache::remember('admin_dashboard_stats', 300, function () {
            return [
                'overview'     => $this->getOverviewStats(),
                'chart'        => $this->getRevenueChartData(), // Logic khó nhất nằm ở đây
                'top_products' => $this->getTopSellingProducts(),
                'low_stock'    => $this->getLowStockProducts(),
            ];
        });
    }

    // 1. Overview Cards (Số liệu tổng quan)
    private function getOverviewStats()
    {
        return [
            // Doanh thu chỉ tính đơn đã hoàn thành/giao hàng thành công
            'total_revenue'    => Order::where('status', 'completed')->sum('total_amount'),
            
            // Đơn mới hôm nay (Bất kể trạng thái nào, trừ nháp nếu có)
            'new_orders_today' => Order::whereDate('created_at', Carbon::today())->count(),
            
            // QUAN TRỌNG: Chỉ số Actionable (Cần xử lý ngay)
            'pending_orders'   => Order::where('status', 'pending')->count(),
            
            'total_customers' => User::whereHas('roles', function ($query) {
                                $query->where('name', 'customer');
                                })->count(),
        ];
    }

    // 2. Low Stock Alert (Cảnh báo nhập hàng)
    private function getLowStockProducts()
    {
        return Product::select('id', 'name', 'stock_qty', 'price')
            ->where('stock_qty', '<', 10) // Ngưỡng cảnh báo là 10
            ->orderBy('stock_qty', 'asc') // Ưu tiên hiển thị hàng sắp hết nhất
            ->limit(5)
            ->get();
    }

    // 3. Top Best Sellers (Sản phẩm bán chạy)
    private function getTopSellingProducts()
    {
        // Join bảng order_items để tính tổng quantity
        // Lưu ý: Chỉ tính các item nằm trong đơn hàng ĐÃ THANH TOÁN hoặc HOÀN THÀNH
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', 'completed') // Chỉ tính đơn thành công
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(products.price * order_items.quantity) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();
    }

    // 4. CHART DATA (Logic lấp đầy ngày trống)
    private function getRevenueChartData()
    {
        $days = 7;
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays($days - 1); // Lấy 7 ngày gần nhất tính cả hôm nay

        // A. Query dữ liệu thô từ DB (Group by Date)
        $rawStats = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('date')
            ->get()
            ->keyBy('date'); // Chuyển mảng thành dạng ['2023-10-01' => Object, ...] để dễ tìm kiếm

        // B. Chuẩn bị mảng kết quả (Format cho Frontend vẽ Chart JS / Recharts)
        $labels = [];
        $values = [];

        // C. Loop qua từng ngày trong khoảng thời gian để lấp đầy dữ liệu
        for ($i = 0; $i < $days; $i++) {
            $dateCheck = $startDate->copy()->addDays($i)->format('Y-m-d');
            
            $labels[] = $dateCheck; // Trục hoành (Ngày)
            
            // Nếu ngày đó có trong DB thì lấy revenue, không thì bằng 0
            $values[] = isset($rawStats[$dateCheck]) ? (int) $rawStats[$dateCheck]->revenue : 0;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
}