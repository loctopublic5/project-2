<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseFormRequest;

class StoreReviewRequest extends BaseFormRequest
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
            'rating'   => ['required', 'integer', 'min:1', 'max:5'], // Chặn số ảo
            'comment'  => ['nullable', 'string', 'max:1000'], // Chặn văn sớ dài dòng
        ];
    }

    public function messages(): array
    {
        return [
            'rating.min' => 'Vui lòng đánh giá ít nhất 1 sao.',
            'rating.max' => 'Đánh giá tối đa là 5 sao.',
        ];
    }
}
