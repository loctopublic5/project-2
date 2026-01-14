<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;

class StoreCategoryRequest extends BaseFormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên danh mục không được để trống.',
            'parent_id.exists' => 'Danh mục cha không tồn tại.',
        ];
    }
}

