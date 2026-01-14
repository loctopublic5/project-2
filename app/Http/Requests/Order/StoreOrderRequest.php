<?php

namespace App\Http\Requests\Order;

use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;

class StoreOrderRequest extends BaseFormRequest
{


    public function rules(): array
    {
        return [
            // 1. Địa chỉ là bắt buộc và phải tồn tại trong DB
            // (Service sẽ check kỹ hơn xem địa chỉ có thuộc về user này không)
            'address_id' => ['required', 'integer', 'exists:user_addresses,id'],

            // 2. Phương thức thanh toán bắt buộc
            'payment_method' => ['required', 'string', Rule::in(['cod', 'wallet', 'vnpay'])],

            // 3. Voucher không bắt buộc, nhưng nếu có phải là chuỗi
            'voucher_code' => ['nullable', 'string', 'max:50'],

            // 4. Ghi chú không bắt buộc, tối đa 500 ký tự
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages()
    {
        return [
            'address_id.required' => 'Vui lòng chọn địa chỉ giao hàng.',
            'address_id.exists'   => 'Địa chỉ không hợp lệ.',
            'payment_method.in'   => 'Phương thức thanh toán không được hỗ trợ.',
        ];
    }
}