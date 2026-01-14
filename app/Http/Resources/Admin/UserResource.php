<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            
            // Nhóm thông tin VIP/Tài chính
            'vip_info' => [
                'total_spending' => (float) $this->total_spending, // Quan trọng: Convert float
                'wallet_balance' => $this->wallet ? (float) $this->wallet->balance : 0,
                'rank' => $this->calculateRank($this->total_spending), // Logic giả định hiển thị rank
            ],

            'status' => $this->is_active ? 'active' : 'banned',
            'is_active' => (bool) $this->is_active,
            
            // Roles (Lấy tên role từ Spatie Permission)
            'roles' => $this->getRoleNames(), 

            'joined_at' => $this->created_at->format('d/m/Y H:i'),

            // Quan hệ: Chỉ load khi được gọi (để tối ưu performance cho list)
            'addresses' => $this->whenLoaded('addresses'),
            'recent_orders' => $this->whenLoaded('orders'),
        ];
    }

    private function calculateRank($spending) {
        if ($spending > 50000000) return 'Diamond';
        if ($spending > 20000000) return 'Gold';
        if ($spending > 5000000) return 'Silver';
        return 'Member';
    }
}