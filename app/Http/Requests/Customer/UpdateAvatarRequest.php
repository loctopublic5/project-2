<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\BaseFormRequest;

class UpdateAvatarRequest extends BaseFormRequest
{
    public function rules()
    {
        return [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Tối đa 2MB
        ];
    }

    public function messages()
    {
        return [
            'avatar.required' => 'Vui lòng chọn một file ảnh.',
            'avatar.image'    => 'File phải là định dạng hình ảnh.',
            'avatar.max'      => 'Dung lượng ảnh không được vượt quá 2MB.',
        ];
    }
}
