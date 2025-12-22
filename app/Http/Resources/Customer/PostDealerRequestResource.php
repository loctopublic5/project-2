<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostDealerRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            // 1. Luôn trả về ID để Frontend biết định danh
            'id' => $this->id,

            // 2. Gọi thẳng thuộc tính (Không cần $this->resource['...'])
            'user_id' => $this->user_id,
            
            // 3. Trả về status (thường là 'pending' lúc mới tạo)
            'status' => $this->status,

            // 4. Format ngày tạo để user biết mình gửi lúc nào
            'created_at' => $this->created_at->format('d/m/Y H:i:s'),
            
            // (Optional) Một dòng text thân thiện để hiển thị UI
            'status_label' => 'Đang chờ duyệt', 
        ];
    }
}