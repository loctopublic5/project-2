<?php

namespace App\Models;



use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory, HasSlug;
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'level',
        'is_active',
    ];

    protected $cats = [
        'is_active' => 'boolean',
        'level'     => 'integer',
        'parent_id' => 'integer',
    ];

/**
     * Quan hệ 1-N: Một danh mục có nhiều sản phẩm
     */
    public function products(): HasMany
    {
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


    // Đệ quy toàn bộ danh mục con
    public function childrenRecursion(){
        return $this->children()->with('childrenRecursion');
    }
}
