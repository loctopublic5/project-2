<?php

namespace App\Http\Requests\Product;

use Illuminate\Validation\Rule;
use App\Http\Requests\BaseFormRequest;

class SaveProductRequest extends BaseFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product') ? $this->route('product')->id : $this->route('id');
    
        // Kiểm tra xem đây là hành động UPDATE hay CREATE
        // Nếu có ID -> Update -> Dùng quy tắc "sometimes" (Chỉ validate nếu có gửi lên)
        // Nếu không có ID -> Create -> Dùng quy tắc "required"
        $isUpdate = !empty($productId); 
        $ruleType = $isUpdate ? 'sometimes' : 'required';
        $productId = $this->route('product') ? $this->route('product')->id : $this->route('id');
        return [
            'category_id' => [$ruleType, 'integer', 'exists:categories,id'],
            'name'        => [$ruleType, 'string', 'max:250'],
            'price'       => [$ruleType, 'numeric', 'min:0'],
            'stock_qty'   => [$ruleType, 'integer', 'min:0'],
        
            'sku'         => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique('products', 'sku')->ignore($productId)],
            'slug'        => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique('products', 'slug')->ignore($productId)],
            'sale_price'  => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],

            'attributes'  => ['nullable', 'array'],
            // 'attributes.*' nghĩa là duyệt qua từng phần tử trong mảng attributes
            'attributes.*.name'  => ['required_with:attributes', 'string', 'max:255'],
            'attributes.*.value' => ['required_with:attributes', 'string', 'max:255'],

            'image'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'gallery'   => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'deleted_images' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            // Category
            'category_id.required' => 'Vui lòng chọn danh mục sản phẩm.',
            'category_id.exists'   => 'Danh mục đã chọn không tồn tại trên hệ thống.',

            // Name
            'name.required' => 'Tên sản phẩm không được để trống.',
            'name.min'      => 'Tên sản phẩm phải có ít nhất :min ký tự.',
            'name.max'      => 'Tên sản phẩm quá dài (tối đa :max ký tự).',

            // SKU
            'sku.unique'     => 'Mã SKU ":input" đã tồn tại, vui lòng chọn mã khác.',
            'sku.alpha_dash' => 'Mã SKU chỉ được chứa chữ, số, dấu gạch ngang (-) và gạch dưới (_).',

            // SLUG
            'slug.unique'     => 'Mã SLUG ":input" đã tồn tại, vui lòng chọn mã khác.',
            'slug.alpha_dash' => 'Mã SLUG chỉ được chứa chữ, số, dấu gạch ngang (-) và gạch dưới (_).',

            // Price
            'price.required' => 'Giá niêm yết là bắt buộc.',
            'price.numeric'  => 'Giá tiền phải là dạng số.',
            'price.min'      => 'Giá tiền không được nhỏ hơn 0.',

            // Sale Price (Logic quan trọng)
            'sale_price.lt'  => 'Giá khuyến mãi (:input) phải NHỎ HƠN giá niêm yết.',

            // Stock
            'stock_qty.integer' => 'Số lượng tồn kho phải là số nguyên.',

            // Attributes (JSON)
            'attributes.array'    => 'Thuộc tính mở rộng phải là định dạng JSON Object hợp lệ.',
            'attributes.*.name.required_with' => 'Tên thuộc tính không được để trống.',
            'attributes.*.value.required_with'=> 'Giá trị thuộc tính không được để trống.',
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'danh mục',
            'sale_price'  => 'giá khuyến mãi',
            'stock_qty'   => 'tồn kho',
        ];
    }
}
