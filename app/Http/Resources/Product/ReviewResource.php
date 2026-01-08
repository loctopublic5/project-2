<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    { 
    return [
        'id'         => $this->id,
        'rating'     => $this->rating,
        'comment'    => $this->comment,
        'created_at' => $this->created_at->format('d/m/Y H:i'),
        'human_time' => $this->created_at->diffForHumans(), // VD: "2 giờ trước"
        
        // Trả về User info để hiện Avatar/Tên người review
        'user'       => [
            'id'        => $this->user_id,
            'full_name' => $this->user->full_name ?? 'Anonymous',
            'avatar'    => $this->user->avatar_url ?? null, // Link ảnh đại diện
        ],
    ];
    }
}
