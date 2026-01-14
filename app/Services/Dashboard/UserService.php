<?php

namespace App\Services\Dashboard;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class UserService
{
    /**
     * CHỨC NĂNG 1: LẤY DANH SÁCH USER (Filter, Search, Sort)
     */
    public function getUsers($params)
    {
        $query = User::query()->with(['wallet', 'roles']); // Eager loading ví và quyền

        // 1. Tìm kiếm (Tên, Email, SĐT)
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('full_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        // 2. Lọc theo trạng thái
        if (isset($params['is_active'])) {
            $query->where('is_active', $params['is_active']);
        }

        // 3. Lọc theo Role (Nếu dùng Spatie/Permission)
        if (!empty($params['role'])) {
            $query->role($params['role']);
        }

        // 4. Sắp xếp (Mặc định mới nhất)
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortDir = $params['sort_dir'] ?? 'desc';
        
        // Hỗ trợ sắp xếp theo tổng chi tiêu để tìm khách sộp ngay ở list
        if ($sortBy === 'total_spending') {
            $query->orderBy('total_spending', $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        return $query->paginate($params['limit'] ?? 10);
    }

    /**
     * Lấy chi tiết User (Kèm địa chỉ và đơn hàng gần nhất)
     */
    public function getUserDetail($id)
    {
        return User::with([
            'wallet',
            'addresses',
            'orders' => function($q) {
                $q->latest()->limit(5); // Chỉ lấy 5 đơn mới nhất để xem nhanh
            }
        ])->findOrFail($id);
    }

    /**
     * Cập nhật trạng thái (Khóa/Mở)
     */
    public function updateStatus($id, $isActive)
    {
        $user = User::findOrFail($id);
        
        // Logic an toàn: Không cho phép khóa Super Admin
        if ($user->hasRole('super_admin')) {
            throw new Exception("Không thể khóa tài khoản Super Admin.");
        }

        $user->update(['is_active' => $isActive]);
        return $user;
    }

    /**
     * CHỨC NĂNG 2: ANALYTICS (Thống kê cho Dashboard)
     */
    public function getAnalytics()
    {
        $now = Carbon::now();

        // 1. Top Spenders (Top 10 Khách hàng chi tiêu nhiều nhất)
        $topSpenders = User::with('wallet')
            ->orderByDesc('total_spending')
            ->limit(10)
            ->get();

        // 2. New Customers (Khách mới trong tháng)
        $newCustomers = User::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->latest()
            ->limit(10) // Lấy danh sách 10 người mới nhất
            ->get();

        // 3. User Stats (Tổng quan)
        $totalUsers = User::count();
        $bannedUsers = User::where('is_active', 0)->count();
        $activeUsers = $totalUsers - $bannedUsers;

        return [
            'stats' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'banned_users' => $bannedUsers,
                'new_this_month_count' => User::whereMonth('created_at', $now->month)->count()
            ],
            'top_spenders' => $topSpenders,
            'new_customers_list' => $newCustomers
        ];
    }
}