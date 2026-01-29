<?php

namespace App\Http\Requests\Customer;

use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;

class UpdateProfileRequest extends BaseFormRequest
{

    public function rules()
    {
        return [
            'full_name' => 'required|string|max:255',
            'phone'     => 'nullable|string|max:15',
            // Kiểm tra email duy nhất nhưng loại trừ email của chính user hiện tại
            'email'     => [
                'required',
                'email',
                Rule::unique('users')->ignore($this->route('id')),
            ],
        ];
    }

    public function messages()
    {
        return [
            'full_name.required' => 'Họ tên không được để trống.',
            'email.required'    => 'Email không được để trống.',
            'email.unique'      => 'Email này đã được sử dụng bởi tài khoản khác.',
        ];
    }
}
