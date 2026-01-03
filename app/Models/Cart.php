<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable =[
        'user_id',
        'session_id',
    ];

    // 1 Cart có nhiều Item bên trong
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // 1 Cart thuộc về 1 User (có thể null nếu là khách vãng lai)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
