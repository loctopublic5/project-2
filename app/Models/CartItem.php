<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'options',
        'selected',
    ];
    protected $casts = [
        'options'  => 'array',   // Tự động chuyển JSON <-> Array
        'selected' => 'boolean',
    ];

    // Item thuộc về 1 Cart
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // Item liên kết tới Product để lấy tên, giá, hình ảnh...
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
