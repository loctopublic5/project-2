<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // $this đại diện cho Model CartItem
        // Ta giả định CartItem đã eager load 'product' từ Service
        $product = $this->product;

        // Logic giá hiển thị (Ưu tiên Sale Price)
        $realPrice = ($product->sale_price && $product->sale_price > 0) 
                        ? $product->sale_price 
                        : $product->price;

        return [
            'item_id'      => $this->id, // ID của dòng trong cart_items (để update/delete)
            'product_id'   => $this->product_id,
            
            // Thông tin sản phẩm (Flat ra cho FE dễ lấy)
            'product_info' => [
                'name'   => $product->name,
                'slug'   => $product->slug,
                'avatar' => $product->avatar, // Giả sử model Product có accessor lấy full URL
                'sku'    => $product->sku,
            ],

            'price'        => (float) $realPrice,      // Giá bán thực tế
            'old_price'    => (float) $product->price, // Giá gốc (để gạch ngang nếu cần)
            
            'quantity'     => (int) $this->quantity,
            'options'      => $this->options,          // JSON size/color
            'selected'     => (bool) $this->selected,  // Trạng thái checkbox
            
            // Thành tiền tạm tính của item này
            'line_total'   => $realPrice * $this->quantity,
            
            // Stock check realtime (Hữu ích cho FE hiện cảnh báo nếu kho hết)
            'in_stock'     => $product->stock_qty >= $this->quantity,
            'max_qty'      => $product->stock_qty, // Để FE limit input số lượng
        ];
    }
}