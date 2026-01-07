<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product_id'   => $this->product_id,
            'product_name' => $this->product_name, // Tên snapshot
            'sku'          => $this->sku,
            'price'        => (float) $this->price_at_purchase, // Giá snapshot
            'quantity'     => $this->quantity,
            'options'      => $this->variant_snapshot, // Size/Color
            'total_line'   => $this->price_at_purchase * $this->quantity,
        ];
    }
}