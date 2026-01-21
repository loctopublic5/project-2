<?php

namespace App\Models;

use App\Traits\HasSlug;
use App\Models\Category;
use App\Models\OrderItem;
use App\Traits\HasUniqueCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Product extends Model
{
    use SoftDeletes, HasSlug, HasUniqueCode, HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'thumbnail',
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
     * Hook vào sự kiện của Model
     */
    protected static function booted(){
        static::creating(function ($product) {
            // 1. Xử lý SLUG (Chuẩn SEO)
            // Luôn tự động tạo slug từ name nếu slug chưa được set hoặc rỗng
            if (empty($product->slug)) {
                $product->slug = $product->generateSlug($product->name);
            }

            // 2. Xử lý SKU (Mã kho)
            // Nếu Admin không nhập SKU -> Tự sinh mã ngẫu nhiên (VD: SP-X8L9P)
            if (empty($product->sku)) {
                // generateUniqueCode(cột, tiền tố, độ dài)
                $product->sku = $product->generateUniqueCode('sku', 'SP', 6);
            }
        });
    }

    /**
     * Accessor: Tự động tạo URL đầy đủ cho thumbnail
     * Giúp code ở Resource hoặc Controller sạch hơn
     */
    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail) {
            return null; // Hoặc return một link ảnh default "no-image.png"
        }

        // Nếu path lưu dưới dạng link tuyệt đối (http...) thì trả về luôn
        if (filter_var($this->thumbnail, FILTER_VALIDATE_URL)) {
            return $this->thumbnail;
        }

        // Mặc định dùng Storage disk public
        return \Illuminate\Support\Facades\Storage::url($this->thumbnail);
    }

    /**
     * Product belongs to Category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(File::class, 'target')->orderBy('id', 'desc');
    }

    public function orderItem(): HasMany{
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany{
        return $this->hasMany(Review::class);
    }

    public function scopeActive($query){
        return $query->where('is_active', true);
    }

    // Quan hệ này ít dùng trực tiếp, nhưng hữu ích khi muốn check:
    // "Sản phẩm này đang nằm trong bao nhiêu giỏ hàng?" (Analytics)
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }



}
