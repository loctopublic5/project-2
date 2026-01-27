<?php 
namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'full_name'  => $this->full_name,
            'email'      => $this->email,
            // Sử dụng Storage::url để lấy link tuyệt đối từ path trong DB
            'avatar_url' => $this->avatar_url 
                            ? asset('storage/' . $this->avatar_url) 
                            : asset('admin_assets/assets/compiled/jpg/1.jpg'),
            'joined_at'  => $this->created_at ? $this->created_at->format('d/m/Y') : '---',
            'vip_info'   => $this->vip_info, // Nếu có liên kết
        ];
    }
}