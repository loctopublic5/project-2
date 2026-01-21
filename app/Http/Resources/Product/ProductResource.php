<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $rawAttributes = $this->resource->attributes; 

        // 2. Decode nếu nó là String
        $specifications = $rawAttributes;
        if (is_string($rawAttributes)) {
            $specifications = json_decode($rawAttributes, true);
        }
        // Đảm bảo luôn là object/array rỗng nếu null
        if (!$specifications) {
            $specifications = [];
        }
        
        $imagesList = [];
        $thumbnail = null;

        if ($this->relationLoaded('images')) {
        $imagesList = $this->images->map(function($file) {
            return [
                'id'  => $file->id,
                'url' => Storage::url($file->path)
            ];
        })->toArray();

        // Lấy ảnh đầu tiên làm thumbnail nếu có
        if (count($imagesList) > 0) {
            $thumbnail = $imagesList[0]['url'];
        }
    }
        return [
            'id'   => $this->id,
            
            // Thông tin định danh
            'info' => [
                'name' => $this->name,
                'sku'  => $this->sku,
                'slug' => $this->slug,
                'description' => $this->description,
                'thumbnail'   => $thumbnail,
            ],

            // Danh mục (Chỉ hiện khi đã load để tối ưu performance)
            'category' => $this->whenLoaded('category', function() {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),

            // Trả về danh sách URL tuyệt đối cho Frontend
            'images' => $imagesList,

            // Giá cả (Logic Pricing Service đính kèm)
            'pricing' => $this->calculated_price ?? [
                'original_price'   => (int) $this->price,
                'sale_price'       => (int) $this->sale_price,
                'is_sale_active'   => $this->sale_price > 0 && $this->sale_price < $this->price,
                'note'             => 'Giá chưa áp dụng Sale'
            ],

            // Kho vận & Thuộc tính
            'inventory' => [
                'stock_qty'   => $this->stock_qty,
                'in_stock'    => $this->stock_qty > 0,
                'status_text' => $this->stock_qty > 0 ? 'Còn hàng' : 'Hết hàng',
            ],

            // Attributes (JSON) - Luôn trả về Object, tránh null
            'specifications' => $specifications, 

            // Meta
            'is_active'  => (bool) $this->is_active,
            'created_at' => $this->created_at->format('d/m/Y H:i'),
        ];
    }
}
