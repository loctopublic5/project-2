<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        
        // 1. Xử lý ảnh sản phẩm (Polymorphic)
        $thumbnailUrl = asset('admin_assets/assets/compiled/jpg/1.jpg'); // Ảnh mặc định nếu không có

        // Kiểm tra xem sản phẩm còn tồn tại không (tránh lỗi null)
        if ($this->product) {
            // Nếu Product có quan hệ 'image' (MorphOne)
            if ($this->product->relationLoaded('images') && $this->product->image) {
                $thumbnailUrl = Storage::url($this->product->image->path);
            } 
            // Fallback: Nếu không Eager Load, thử truy cập trực tiếp (Lazy Load)
            elseif ($this->product->image) {
                $thumbnailUrl = Storage::url($this->product->image->path);
            }
        }
        return [
            'id'           => $this->id,
            'product_id'   => $this->product_id,
            'product_name' => $this->product_name, // Tên lúc mua (Snapshot)
            'sku'          => $this->sku,
            
            // Giá lúc mua
            'price'        => (float) $this->price_at_purchase,
            'quantity'     => $this->quantity,
            'total_line'   => (float) ($this->price_at_purchase * $this->quantity),
            
            'options'      => $this->variant_snapshot, // Size/Color

            // Lấy thêm ảnh từ relationship 'product' (nếu sản phẩm chưa bị xóa)
            // Nếu product null (đã bị xóa cứng), trả về ảnh mặc định
            'thumbnail'    => $thumbnailUrl,
            
            // Slug để user bấm vào xem lại sản phẩm hiện tại
            'product_slug' => $this->product->slug ?? null,
        ];
    }
}