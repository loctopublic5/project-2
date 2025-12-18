<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    // 1. Hàm chuẩn hóa ngày tháng (Helper)
    // Các class con có thể gọi $this->formatDate(...)
    protected function formatDate($date)
    {
        return $date ? $date->format('Y-m-d H:i:s') : null;
    }

    // 2. Cấu trúc trả về mặc định (Optional)
    // Laravel Resource mặc định bọc data trong key "data".
    // Ta có thể dùng hàm with() để chèn thêm meta data (status, message).
    public function with(Request $request): array
    {
        return [
            'status' => true,
            'message' => 'Lấy dữ liệu thành công',
        ];
    }
}