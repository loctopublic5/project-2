<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Products extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'slug',
        'price',
        'sale_price',
        'dealer_price',
        'stock_qty',
        'description',
        'view_count',
    ];

    public function categories():BelongsToMany{
        return $this->belongsToMany(categories::class,'products', 'category_id', 'id');
    }
}
