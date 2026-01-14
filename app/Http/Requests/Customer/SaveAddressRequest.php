<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\BaseFormRequest;

class SaveAddressRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone'          => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:9', 'max:15'], // Regex cho SĐT
            
            // Các ID địa chính (bắt buộc phải là số)
            'province_id'    => ['required', 'integer'],
            'district_id'    => ['required', 'integer'],
            'ward_id'        => ['required', 'integer'],
            
            'address_detail' => ['required', 'string', 'max:255'],
            'is_default'     => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Vui lòng nhập tên người nhận.',
            'phone.regex' => 'Số điện thoại không đúng định dạng.',
            'province_id.required' => 'Vui lòng chọn Tỉnh/Thành phố.',
            'district_id.required' => 'Vui lòng chọn Quận/Huyện.',
            'ward_id.required'     => 'Vui lòng chọn Phường/Xã.',
            'address_detail.required'=> "Vui lòng nhập thông tin địa chỉ chi tiết."
        ];
    }
}
