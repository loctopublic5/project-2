<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateDealerRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            // Nên trả về cả thông tin user để Admin biết vừa duyệt cho ai
            'user_id' => $this->user_id,
            'user_name' => $this->user ? $this->user->full_name : 'Unknown User',
            
            'status' => $this->status,
            
            // --- XỬ LÝ ĐIỀU KIỆN ẨN/HIỆN ---
            
            // 1. admin_note: Chỉ hiện key này nếu giá trị khác null
            'admin_note' => $this->whenNotNull($this->admin_note),

            // 2. approved_at: Chỉ hiện nếu đã duyệt. Format lại ngày giờ cho đẹp.
            // Dùng toán tử ?-> để tránh lỗi nếu approved_at đang null
            'approved_at' => $this->whenNotNull($this->approved_at?->format('d/m/Y H:i:s')),
            
            // Thêm thời gian tạo yêu cầu để dễ đối chiếu
            'created_at' => $this->created_at->format('d/m/Y H:i:s'),
        ];
    }
}