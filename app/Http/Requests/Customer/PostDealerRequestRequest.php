<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\BaseFormRequest;

class PostDealerRequestRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required','string'],
            'status' => ['required_if:status,pending']
        ];
    }

    public function messages():array
    {
        return [
            'user_id.required' => 'Vui lòng cung cấp id User của bạn!',
            'status.required_if' => 'Tráng thái duyệt mặc định là pending',
        ];
    }
}
