<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use SoftDeletes;

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
        'is_active',
    ];

    /**
     * Cast database fields
     */
    protected $casts = [
        'attributes' => 'array',
        'price' => 'float',
        'sale_price' => 'float',
        'stock_qty' => 'integer',
        'view_count' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Product belongs to Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
