<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'level',
        'is_active',
    ];

/**
     * Quan hệ 1-N: Một danh mục có nhiều sản phẩm
     */
    public function products(): HasMany
    {
        // SAI: return $this->hasMany('products', ...); 
        // ĐÚNG: Phải gọi Model class
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    /**
     * Quan hệ đệ quy (Recursive): Lấy danh mục cha
     * Để làm Breadcrumb (Trang chủ > Điện tử > Điện thoại)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Quan hệ đệ quy: Lấy các danh mục con
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
