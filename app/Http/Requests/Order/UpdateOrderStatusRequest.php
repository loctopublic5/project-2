<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseFormRequest;

class UpdateOrderStatusRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // 1. Status bắt buộc phải nằm trong danh sách cho phép
            'status' => [
                'required',
                'string',
                'in:confirmed,shipping,cancelled,completed' 
            ],

            // 2. Lý do (quan trọng khi Hủy đơn, các trạng thái khác có thể null)
            'reason' => [
                'nullable',
                'string',
                'max:255'
            ],
        ];
    }

    /**
     * Custom message tiếng Việt
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng chọn trạng thái đơn hàng.',
            'status.in'       => 'Trạng thái không hợp lệ. Chỉ chấp nhận: confirmed, shipping, cancelled, completed.',
            'reason.max'      => 'Lý do không được vượt quá 255 ký tự.',
        ];
    }
}