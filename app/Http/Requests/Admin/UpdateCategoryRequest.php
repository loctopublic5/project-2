<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;

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
                    if ($value == $this->category->id) {
                        $fail('Danh mục cha không thể là chính danh mục này.');
                    }
                },
            ],
            'is_active' => 'boolean',
        ];
    }
}