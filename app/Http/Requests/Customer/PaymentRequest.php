<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\BaseFormRequest;

class PaymentRequest extends BaseFormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'note'     => ['nullable', 'string', 'max:255']
        ];
    }

    public function messages():array{
        return [
            'order_id.required' => 'Cung cấp đủ id đơn hàng',
        ];
    }
}
