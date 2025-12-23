<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;



class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'slug',
        'price',
        'sale_price',
        'stock_qty',
        'description',
        'attributes',
        'view_count',
    ];

    protected $cats =[
        'attributes' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class); 
    }
}
