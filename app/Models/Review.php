<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'rating',
        'comment',
        'pages_url'
    ];

    public function product(): BelongsTo{
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }
}


