<?php

namespace App\Models;

use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function images(): MorphMany
    {
        return $this->morphMany(File::class, 'target');
    }

    public function orderItem(): HasMany{
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany{
        return $this->hasMany(Review::class);
    }

    public function scopeActive($query){
        return $this->where('is_active', true);
    }

}
