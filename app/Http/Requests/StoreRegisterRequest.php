<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreRegisterRequest extends BaseFormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required','string','max:100'],
            'email' => ['required','string', 'email', 'unique:users,email'],
            'phone' => ['nullable','max:20','regex:/^(84|0[3|5|7|8|9])([0-9]{8})$/','unique:users,phone'],
            'password' => ['required','string','confirmed'],
        ];
    }
    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ tên đầy đủ.',
            'email.unique' => 'Email này đã được đăng ký.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'phone.regex' => 'Số điện thoại không đúng định dạng Việt Nam.',
        ];
    }
}
