<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'recipient_name' => $this->recipient_name,
            'phone'          => $this->phone,
            
            // Gom nhóm location để FE dễ binding
            'location'       => [
                'province_id' => $this->province_id,
                'district_id' => $this->district_id,
                'ward_id'     => $this->ward_id,
                'detail'      => $this->address_detail,
            ],
            
            // Trả về full address dạng string để hiển thị nhanh (nếu cần)
            // Lưu ý: Ở đây ta chỉ nối chuỗi đơn giản vì chưa có bảng Province/District
            'full_address'   => "{$this->address_detail}", 
            
            'is_default'     => (boolean) $this->is_default,
            'created_at'     => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}