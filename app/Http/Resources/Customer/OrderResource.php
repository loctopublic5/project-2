<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Customer\OrderItemResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Check quyền Admin (Giả sử bạn có hàm check isAdmin, hoặc check permission)
        // Nếu chưa có, tạm thời để false hoặc logic đơn giản.
        // Ví dụ: $isAdmin = $request->user() && $request->user()->tokenCan('admin:view'); 
        // Ở đây tôi demo logic: Nếu request gọi từ route admin (prefix) thì hiện.
        $isAdminView = $request->is('api/v1/admin/*'); 

        return [
            'id'             => $this->id,
            'code'           => $this->code,
            
            // --- ENUM FORMATTING (Frontend cực thích cái này) ---
            'status'         => [
                'key'   => $this->status->value,       // Để FE dùng trong logic code (pending)
                'label' => $this->status->label(),     // Để hiển thị (Chờ xử lý)
                'color' => $this->status->color(),     // Để tô màu (warning)
            ],
            
            'payment_status' => [
                'key'   => $this->payment_status->value,
                'label' => $this->payment_status->label(),
            ],
            
            'payment_method' => strtoupper($this->payment_method),

            // --- MONEY FORMATTING ---
            'subtotal'       => (float) $this->subtotal,
            'shipping_fee'   => (float) $this->shipping_fee,
            'discount'       => (float) $this->discount_amount,
            'tax'            => (float) $this->tax,
            'total'          => (float) $this->total_amount,

            // --- DETAILS ---
            // Shipping Address đã được Model cast sang Array, ta trả về luôn
            'shipping_address' => $this->shipping_address,
            'note'             => $this->note,
            
            'created_at'       => $this->created_at->format('d/m/Y H:i'),
            
            // --- CONDITIONAL RELATIONS (DRY) ---
            // Chỉ hiện items khi đã được load (Detail view)
            'items'            => OrderItemResource::collection($this->whenLoaded('items')),

            // Chỉ hiện User info khi Admin xem (và đã eager load từ Service)
            // Bạn cần tạo UserResource nếu chưa có, hoặc trả về array đơn giản
            'customer'         => $this->whenLoaded('user', function () {
                return [
                    'id'    => $this->user->id,
                    'name'  => $this->user->name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone ?? 'N/A',
                ];
            }),

            // --- ADMIN ONLY FIELDS ---
            // Ví dụ: Ghi chú nội bộ của CSKH (chỉ Admin thấy)
            'internal_note' => $this->when($isAdminView, $this->internal_note),
        ];
    }
}