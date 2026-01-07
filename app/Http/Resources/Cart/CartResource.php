<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Input của Resource này là mảng: 
        // [
        //    'cart' => $cartModel, 
        //    'pricing' => $pricingResult array
        // ]
        
        $cartModel = $this['cart'];
        $pricing   = $this['pricing'];

        return [
            'cart_id'    => $cartModel->id,
            
            // Transform list items bằng Resource con
            'items'      => CartItemResource::collection($cartModel->items),
            
            // Phần tổng kết tiền nong (Lấy từ kết quả PricingService)
            'summary'    => [
                'subtotal'        => $pricing['subtotal'],        // Tổng tiền hàng
                'discount_amount' => $pricing['discount_amount'], // Tổng giảm giá
                'shipping_fee'    => $pricing['shipping_fee'],    // Phí ship
                'final_total'     => $pricing['total'],           // Khách phải trả
                
                // Thông tin voucher (nếu có)
                'voucher_applied' => $pricing['voucher'] ? [
                    'code'           => $pricing['voucher']['code'],
                    'discount_value' => $pricing['voucher']['discount_value'],
                    'type'           => $pricing['voucher']['type'],
                ] : null,
            ],
            
            // Metadata
            'updated_at' => $cartModel->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}