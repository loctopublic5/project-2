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

            'avatar_url' => $this->avatar_url 
                ? asset('storage/' . $this->avatar_url) 
                : asset('admin_assets/assets/compiled/jpg/1.jpg'),
            
            // Nhóm thông tin VIP/Tài chính
            'vip_info' => [
                'total_spending' => (float) $this->total_spending, // Quan trọng: Convert float
                'wallet_balance' => $this->wallet ? (float) $this->wallet->balance : 0,
                'rank' => $this->calculateRank($this->total_spending), // Logic giả định hiển thị rank
            ],

            'status' => $this->is_active ? 'active' : 'banned',
            'is_active' => (bool) $this->is_active,
            
            // Lấy trực tiếp từ quan hệ roles() đã định nghĩa trong Model
            // pluck('name') sẽ lấy cột name trong bảng roles trả về mảng ['admin', 'manager']
            'roles' => $this->whenLoaded('roles', function() {
                return $this->roles->pluck('name');
            }), 

            'joined_at' => $this->created_at->format('d/m/Y H:i'),

            // Quan hệ: Chỉ load khi được gọi (để tối ưu performance cho list)
            'addresses' => $this->whenLoaded('addresses'),
            'recent_orders' => $this->whenLoaded('orders'),
        ];
    }

    private function calculateRank($spending) {
        if ($spending > 1000000000) return 'Diamond';
        if ($spending > 400000000) return 'Gold';
        if ($spending > 10000000) return 'Silver';
        return 'Member';
    }
}