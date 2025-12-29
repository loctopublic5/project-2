<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\BaseFormRequest;


class RefundRequest extends BaseFormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['Required', 'exists:user,id'],
            'amount'  => ['required', 'integer', 'min:10000'],
            'original_order_id' => ['required', 'string'],
            'reason'  => ['required', 'string', 'min:5']
        ];
    }

    public function messages():  array{
        return [
            'user_id.required' => 'Cung cấp đầy đủ User_id',
            'amount.min'       => 'Số tiền tối thiểu là 10.000 VNĐ',
            'original_order_id.required' => 'Cung cấp đầy đủ mã đơn hàng cần hoàn tiền',
            'reason.required'  => 'Cung cấp đủ lý do hoàn tiền đơn này là gì'
        ];
    }
}
