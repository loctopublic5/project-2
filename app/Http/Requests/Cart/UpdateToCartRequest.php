<?php

namespace App\Http\Requests\Cart;

use App\Http\Requests\BaseFormRequest;

class UpdateCartItemRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            // Dùng 'sometimes' để linh động: Chỉ validate nếu field đó có trong request
            'quantity' => ['sometimes', 'integer', 'min:1'],
            
            'options'  => ['sometimes', 'nullable', 'array'],
        ];
    }
    public function messages(): array
    {
        return [
            'quantity.min' => 'Số lượng phải lớn hơn 0.',
        ];
    }
}