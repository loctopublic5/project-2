<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
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
    ];

    /**
     * TỰ ĐỘNG SINH SLUG KHI TẠO MỚI (Model Event)
     */
    protected static function boot()
    {
        parent::boot();

        // Sự kiện "creating": Chạy ngay trước khi lệnh INSERT được gửi xuống DB
        static::creating(function ($model) {
            // Nếu slug chưa có hoặc bị rỗng -> Tự sinh từ name
            if (empty($model->slug)) {
                // Gọi hàm generateSlug từ Trait (protected vẫn gọi được vì đang ở trong class)
                $model->slug = $model->generateSlug($model->name);
            }
        });
        
        // Optional: Sự kiện "updating": Nếu muốn đổi tên thì đổi luôn slug (cẩn thận SEO)
        
        static::updating(function ($model) {
            if ($model->isDirty('name') && !$model->isDirty('slug')) {
                $model->slug = $model->generateSlug($model->name);
            }
        });
        
    }
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
