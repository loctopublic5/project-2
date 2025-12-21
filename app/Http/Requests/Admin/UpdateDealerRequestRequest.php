<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;

class UpdateDealerRequestRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:approved,rejected'],
            'admin_note' => ['required_if:status,rejected', 'string', 'nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Bạn cần đồng ý hoặc từ chối',
            'admin_note.required_if' => 'Hãy viết lý do từ chối của bạn',
        ];
    }
}
