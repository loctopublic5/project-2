<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;


    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',      // Snapshot tên SP
        'sku',               // Snapshot SKU
        'variant_snapshot',  // Snapshot Size/Màu (JSON)
        'quantity',
        'price_at_purchase', // Snapshot Giá lúc mua
    ];

    // Tắt timestamps vì bảng order_items không có (và không cần) cột created_at/updated_at
    public $timestamps = false;
    protected $casts = [
        'price_at_purchase' => 'float',
        'variant_snapshot'  => 'array', // Tự động decode JSON
    ];

    // --- RELATIONSHIPS ---

    // Thuộc về đơn hàng nào
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Liên kết ngược về Product gốc (để lấy ảnh, hoặc link sản phẩm)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}