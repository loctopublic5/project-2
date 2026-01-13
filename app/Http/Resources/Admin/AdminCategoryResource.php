<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,
            'parent_name' => $this->parent ? $this->parent->name : null, // Hiển thị tên cha cho dễ nhìn
            'level' => $this->level,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->format('d/m/Y H:i'),
            // Chỉ load children khi cần thiết (tránh N+1 query nếu không eager load)
            'children' => AdminCategoryResource::collection($this->whenLoaded('childrenRecursive')),
        ];
    }
}