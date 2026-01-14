<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Middleware Admin đã xử lý
    }

    public function rules()
    {
        return [
            'is_active' => 'required|boolean', // 1: Active, 0: Inactive
        ];
    }
}