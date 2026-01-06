<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code'           => $this->code,
            'status'         => $this->status, // pending, confirmed...
            'status_text'    => ucfirst($this->status), // Tiện cho FE hiển thị
            
            'payment_method' => strtoupper($this->payment_method),
            'payment_status' => $this->payment_status,
            
            // Tài chính
            'subtotal'       => (float) $this->subtotal,
            'shipping_fee'   => (float) $this->shipping_fee,
            'discount'       => (float) $this->discount_amount,
            'total'          => (float) $this->grand_total,
            
            // Thông tin giao hàng (Lấy từ Snapshot JSON)
            // Laravel tự cast mảng này nếu khai báo trong model, resource chỉ việc trả về
            'shipping_info'  => $this->shipping_address, 
            'note'           => $this->note,
            
            'created_at'     => $this->created_at->format('d/m/Y H:i'),
            
            // Nhúng danh sách item đã format
            'items'          => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}