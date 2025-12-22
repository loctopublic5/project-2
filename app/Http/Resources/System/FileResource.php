<?php

namespace App\Http\Resources\System;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            // Quan trọng: Biến path tương đối thành URL tuyệt đối để frontend hiển thị
            // Nếu bạn đã làm Accessor 'url' ở Model thì dùng: $this->url
            // Nếu chưa, dùng Storage::url() trực tiếp tại đây:
            'url' => $this->disk === 'public' ? $this->url : null,
            
            'mime_type' => $this->mime_type,
            'size_kb' => round($this->size / 1024, 2), // Đổi sang KB cho dễ nhìn
            'created_at' => $this->created_at->format('d/m/Y H:i:s'),
        ];
    }
}