<?php

namespace App\Services\Dashboard;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class UserService
{
    /**
     * CHỨC NĂNG 1: LẤY DANH SÁCH KHÁCH HÀNG (Chỉ Customer)
     */
    public function getUsers($params)
{
    $query = User::query()
        ->with(['wallet', 'roles'])
        ->withSum(['orders as total_spending' => function($q) {
            $q->where('status', 'completed');
        }], 'total_amount');

    // Chỉ lấy Customer
    $query->whereHas('roles', function($q) {
        $q->where('name', 'customer');
    });

    // 1. Tìm kiếm
    if (!empty($params['keyword'])) {
        $keyword = $params['keyword'];
        $query->where(function ($q) use ($keyword) {
            $q->where('full_name', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%")
                ->orWhere('phone', 'like', "%{$keyword}%");
        });
    }

    // 2. Filter Status (Sửa lại logic map từ 'active'/'blocked' sang 1/0)
    // Frontend gửi: status = 'active' hoặc 'blocked'
    if (!empty($params['status'])) {
        if ($params['status'] === 'active') {
            $query->where('is_active', 1);
        } elseif ($params['status'] === 'blocked') {
            $query->where('is_active', 0);
        }
    }

    // --- 3. SORT LOGIC (SỬA LẠI ĐỂ BẮT ĐÚNG PARAM) ---
    
    // Ưu tiên 1: Nếu có yêu cầu sort theo tiền
    if (!empty($params['sort_spending'])) {
        // params['sort_spending'] sẽ là 'asc' hoặc 'desc'
        $query->orderBy('total_spending', $params['sort_spending']);
    } 
    // Ưu tiên 2: Sort mặc định
    else {
        // Logic sort các cột khác (nếu cần mở rộng sau này)
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortDir = $params['sort_dir'] ?? 'desc';

        if (in_array($sortBy, ['full_name', 'email', 'created_at'])) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->latest(); // Mặc định là mới nhất lên đầu
        }
    }

    return $query->paginate($params['limit'] ?? 10);
}

    /**
     * Lấy chi tiết User (Kèm địa chỉ, đơn hàng, và tổng chi tiêu)
     */
    public function getUserDetail($id)
    {
        return User::with([
            'wallet',
            'addresses',
            'roles', // Load thêm role để hiển thị
            'orders' => function($q) {
                $q->latest()->limit(5); 
            }
        ])
        // QUAN TRỌNG: Cần tính tổng chi tiêu ở đây để hiển thị Ranking/VIP Info
        ->withSum(['orders as total_spending' => function($q) {
            $q->where('status', 'completed');
        }], 'total_amount')
        ->findOrFail($id);
    }

    /**
     * Cập nhật trạng thái (Khóa/Mở)
     */
    public function updateStatus($id)
    {
        $user = User::findOrFail($id);
        
        // Logic an toàn: Dùng hàm hasRole thủ công bạn đã viết trong Model
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            throw new Exception("Không thể khóa tài khoản Quản trị viên.");
        }

        // Đảo ngược trạng thái hiện tại (Toggle)
        // Nếu đang 1 -> thành 0, đang 0 -> thành 1
        $newStatus = !$user->is_active;
        $user->update(['is_active' => $newStatus]);

        return $user;
    }

    /**
     * CHỨC NĂNG 2: ANALYTICS
     */
    public function getAnalytics()
    {
        $now = Carbon::now();

        // Query cơ bản chỉ đếm Customer
        $customerQuery = User::whereHas('roles', function($q) {
            $q->where('name', 'customer');
        });

        // 1. Stats (FIX KEY CHO KHỚP VỚI JS)
        // JS đang gọi: stats.total, stats.active...
        $total = (clone $customerQuery)->count();
        $banned = (clone $customerQuery)->where('is_active', 0)->count();
        $active = $total - $banned;
        $newThisMonth = (clone $customerQuery)->whereMonth('created_at', $now->month)
                                                ->whereYear('created_at', $now->year)->count();

        // 2. Top Spenders (Chỉ tính customer)
        $topSpenders = User::whereHas('roles', fn($q) => $q->where('name', 'customer'))
            ->with('wallet')
            ->withSum(['orders as total_spending' => function($q) {
                $q->where('status', 'completed');
            }], 'total_amount')
            ->orderByDesc('total_spending')
            ->limit(10)
            ->get();

        // 3. New Customers
        $newCustomers = (clone $customerQuery)
        ->withSum(['orders as total_spending' => function($q) {
            $q->where('status', 'completed');
        }], 'total_amount') // <--- THÊM DÒNG NÀY
        ->latest()
        ->limit(10)
        ->get();

        return [
            'stats' => [
                'total' => $total,          // JS gọi id="stat-total"
                'active' => $active,        // JS gọi id="stat-active"
                'banned' => $banned,        // JS gọi id="stat-banned"
                'new_this_month' => $newThisMonth // JS gọi id="stat-new"
            ],
            'top_spenders' => $topSpenders,
            'new_customers_list' => $newCustomers
        ];
    }
}