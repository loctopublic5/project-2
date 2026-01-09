<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID   = 'unpaid';
    case PAID     = 'paid';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::UNPAID   => 'Chưa thanh toán',
            self::PAID     => 'Đã thanh toán',
            self::REFUNDED => 'Đã hoàn tiền',
        };
    }
}