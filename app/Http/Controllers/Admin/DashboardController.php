<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    use ApiResponse;
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * GET /api/v1/admin/dashboard
     */
    public function index()
    {
        // 1. Gọi Service lấy số liệu
        $data = $this->dashboardService->getDashboardData();

        // 2. Trả về JSON chuẩn
        return $this->success($data, 'Lấy dữ liệu dashboard thành công.');
    }

    /**
     * (Optional) API để xóa Cache Dashboard thủ công
     * GET /api/v1/admin/dashboard/refresh
     * Dùng khi Admin muốn thấy số liệu mới nhất ngay lập tức
     */
    public function refresh()
    {
        Cache::forget('admin_dashboard_stats');
        
        return $this->success(null, 'Đã xóa cache dashboard. Số liệu mới sẽ được tải lại ở lần truy cập tiếp theo.');
    }
}