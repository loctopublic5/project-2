<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\BaseFormRequest;

class DepositRequest extends BaseFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'amount'      => 'required|integer|min:10000|max:50000000',
                'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array{
        return [
            'amount.required' => 'Vui lòng cung cấp đủ thông tin',
            'amount.min'      => 'Giá trị tối thiếu 10.000 VNĐ',
            'amount.max'      => 'Giá trị tối đa 500.000 VNĐ',
        ];
    }
}
