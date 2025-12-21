<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseFormRequest;



class LoginRequest extends BaseFormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required','string','email'],
            'password'=> ['required','string']
        ];
    }

    public function messages(): array{
        return [
            'email.required' => 'Cung cấp đầy đủ email',
            'password.required' => 'Vui lòng cung cấp mật khẩu'
        ];
    }
}
