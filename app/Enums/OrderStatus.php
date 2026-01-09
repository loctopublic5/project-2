<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING   = 'pending';
    case CONFIRMED = 'confirmed';
    case SHIPPING  = 'shipping';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case RETURNED  = 'returned';

    // Helper để hiển thị ra Frontend đẹp (Human Readable)
    public function label(): string
    {
        return match($this) {
            self::PENDING   => 'Chờ xử lý',
            self::CONFIRMED => 'Đã xác nhận',
            self::SHIPPING  => 'Đang vận chuyển',
            self::COMPLETED => 'Hoàn thành',
            self::CANCELLED => 'Đã hủy',
            self::RETURNED  => 'Trả hàng',
        };
    }
    
    // Helper màu sắc cho Badge (Dùng cho cả Admin/User view)
    public function color(): string
    {
        return match($this) {
            self::PENDING   => 'warning', // Vàng
            self::CONFIRMED => 'info',    // Xanh dương
            self::SHIPPING  => 'primary', // Xanh đậm
            self::COMPLETED => 'success', // Xanh lá
            self::CANCELLED => 'danger',  // Đỏ
            self::RETURNED  => 'secondary', // Xám
        };
    }
}