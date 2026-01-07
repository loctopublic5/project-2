<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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
            'thumbnail'    => $this->product->thumbnail ?? 'default-product.png',
            
            // Slug để user bấm vào xem lại sản phẩm hiện tại
            'product_slug' => $this->product->slug ?? null,
        ];
    }
}