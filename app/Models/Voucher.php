<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_value',
        'max_discount_amount',
        'quantity',
        'start_date',
        'end_date',
    ];

    protected $cats =[
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'is_active'  => 'boolean',
        'value'      => 'decimal:2',
    ];

    /**
    * Scope lấy các voucher khả dụng:
    * 1. Đang kích hoạt (is_active = true)
    * 2. Còn số lượng (quantity > 0)
    * 3. Trong khung giờ (start <= now <= end)
    */
    public function scopeAvailable($query){
        $now = now();
        return $query->where('is_active', true)
                    ->where('quantity', '>', 0)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
    }
}
