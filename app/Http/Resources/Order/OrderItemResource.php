<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        
        // Ưu tiên lấy thumbnail trực tiếp từ product, nếu không có thì dùng ảnh mặc định
        $thumbnailUrl = asset('admin_assets/assets/compiled/jpg/1.jpg');

        if ($this->product && $this->product->thumbnail) {
            // Kiểm tra xem là URL tuyệt đối hay là đường dẫn trong Storage
            $thumbnailUrl = filter_var($this->product->thumbnail, FILTER_VALIDATE_URL) 
                ? $this->product->thumbnail 
                : Storage::url($this->product->thumbnail);
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