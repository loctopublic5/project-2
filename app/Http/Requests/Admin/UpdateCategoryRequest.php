<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;
use App\Models\Category; // Nhớ import Model nếu cần check instanceof

class UpdateCategoryRequest extends BaseFormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            // Ngăn chặn chọn chính mình làm cha
            'parent_id' => [
                'nullable', 
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    // Lấy ID từ route (URL). Tham số này tên là 'category' hoặc 'id' tùy route của bạn
                    // Đoạn code dưới đây sẽ tự động xử lý an toàn:
                    $currentCategory = $this->route('category') ?? $this->route('id');

                    // Nếu Laravel tự động convert sang Model (Model Binding) thì lấy id
                    if ($currentCategory instanceof Category) {
                        $currentId = $currentCategory->id;
                    } else {
                        // Nếu không, nó chính là ID dạng string/int
                        $currentId = $currentCategory;
                    }

                    if ($value == $currentId) {
                        $fail('Danh mục cha không thể là chính danh mục này.');
                    }
                },
            ],
            'is_active' => 'boolean',
        ];
    }
}