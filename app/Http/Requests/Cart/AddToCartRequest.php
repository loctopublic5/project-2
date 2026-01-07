<?php

namespace App\Http\Requests\Cart;

use App\Http\Requests\BaseFormRequest;

class AddToCartRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            // Check ID tồn tại VÀ đang active (tránh add hàng đã ẩn)
            'product_id' => ['required', 'integer', 'exists:products,id,is_active,1'],
            
            'quantity'   => ['required', 'integer', 'min:1'],
            
            // Options: Cho phép null, nhưng nếu có phải là Array
            // VD: ['size' => 'M', 'color' => 'Red']
            'options'    => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.',
            'quantity.min'      => 'Số lượng phải lớn hơn 0.',
        ];
    }
}