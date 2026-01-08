<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;
    /**
     * GET /api/notifications
     * Lấy danh sách thông báo (Phân trang)
     */
    public function index(Request $request)
    {
        // Lấy tất cả thông báo của user hiện tại
        $notifications = $request->user()
            ->notifications() // Truy vấn vào bảng notifications
            ->latest()
            ->paginate(10);

        return $this->success($notifications, 'Lấy danh sách thông báo thành công.');
    }

    /**
     * PATCH /api/notifications/{id}/read
     * Đánh dấu 1 thông báo là Đã đọc
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->markAsRead(); // Cập nhật cột read_at
        }

        return $this->success(null, 'Đã đánh dấu đã đọc');
    }

    /**
     * PATCH /api/notifications/read-all
     * Đánh dấu TẤT CẢ là đã đọc
     */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return $this->success(null, 'Đã đọc tất cả');
    }
}
