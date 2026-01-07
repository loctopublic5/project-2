<?php

namespace App\Models;

use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    // Khai báo các cột được phép gán dữ liệu (Mass Assignment)
    // Phải khớp với file migration đã tạo
    protected $fillable = [
        'user_id',
        'code',             // Mã đơn (ORD-XXX)
        'subtotal',         // Tổng tiền hàng
        'shipping_fee',     // Phí ship
        'voucher_discount', // Tiền giảm giá
        'total_amount',     // Tổng thanh toán cuối cùng
        'payment_method',   // 'cod', 'wallet'
        'payment_status',   // 'unpaid', 'paid'
        'status',           // 'pending', 'processing', 'shipping', 'completed', 'cancelled'
        'shipping_address', // JSON snapshot địa chỉ
        'note'              // Ghi chú của khách
    ];

    // Ép kiểu dữ liệu (Casting) tự động
    protected $casts = [
        'total_amount'     => 'float',
        'subtotal'         => 'float',
        'shipping_fee'     => 'float',
        'voucher_discount' => 'float',
        'shipping_address' => 'array', // Tự động decode JSON thành Array PHP
        'created_at'       => 'datetime',
    ];

    // --- RELATIONSHIPS (Quan hệ) ---

    // 1 đơn hàng thuộc về 1 User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 1 đơn hàng có nhiều món hàng (Items)
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function voucherUsage(): HasMany{
        return $this->hasMany(VoucherUsage::class);
    }

    // --- HELPER METHODS (Logic nghiệp vụ) ---

    // Kiểm tra xem đơn này có được phép thanh toán không?
    // Dùng trong PaymentController
    public function canBePaid()
    {
        if ($this->payment_status === 'paid') return false;
        if ($this->status === 'cancelled') return false;
        return true;
    }
}