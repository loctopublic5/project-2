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
        // Lấy ID sản phẩm từ URL (nếu đang update)
        // Giả sử Route là: PUT /products/{id} -> thì lấy $this->route('id')
        // Nếu Route Model Binding: PUT /products/{product} -> thì lấy $this->route('product')->id
        $productId = $this->route('product') ? $this->route('product')->id : $this->route('id');
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:250'],
            'sku'         => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique('products', 'sku')->ignore($productId)],
            'slug'        => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique('products', 'slug')->ignore($productId)],
            'price'       => ['required', 'numeric', 'min:0'],
            'sale_price'  => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock_qty'   => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],

            'attributes'  => ['nullable', 'array'],
            // 'attributes.*' nghĩa là duyệt qua từng phần tử trong mảng attributes
            'attributes.*' => ['nullable', 'string', 'max:255'],
            //Nếu muốn bắt buộc phải có key cụ thể (VD: material)
            // 'attributes.material' => ['required', 'string'],
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
            'attributes.*.string' => 'Giá trị của thuộc tính phải là dạng chuỗi (không hỗ trợ mảng con).',
            'attributes.*.max'    => 'Giá trị thuộc tính quá dài (tối đa 255 ký tự).',
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
