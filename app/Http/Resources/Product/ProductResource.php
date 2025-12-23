<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            
            // Thông tin định danh
            'info' => [
                'name' => $this->name,
                'sku'  => $this->sku,
                'slug' => $this->slug,
            ],

            // Danh mục (Chỉ hiện khi đã load để tối ưu performance)
            'category' => $this->whenLoaded('category', function() {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),

            // Giá cả (Logic Pricing Service đính kèm)
            'pricing' => $this->calculated_price ?? [
                'original_price'   => (int) $this->price,
                'sale_price'       => (int) $this->sale_price,
                'is_sale_active'   => $this->sale_price > 0 && $this->sale_price < $this->price,
                'note'             => 'Giá chưa áp dụng chính sách đại lý'
            ],

            // Kho vận & Thuộc tính
            'inventory' => [
                'stock_qty'   => $this->stock_qty,
                'in_stock'    => $this->stock_qty > 0,
                'status_text' => $this->stock_qty > 0 ? 'Còn hàng' : 'Hết hàng',
            ],

            // Attributes (JSON) - Luôn trả về Object, tránh null
            'specifications' => $this->attributes ?? (object)[], 

            // Meta
            'is_active'  => (bool) $this->is_active,
            'created_at' => $this->created_at->format('d/m/Y H:i'),
        ];
    }
}
