<?php

namespace App\Http\Controllers\Admin; 

use App\Http\Controllers\Controller;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected $productService;

    // Sử dụng Dependency Injection để gọi Service
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Hiển thị danh sách sản phẩm
     */
    public function index(Request $request)
    {
        // FIX: Đảm bảo truyền mảng vào hàm list
        $products = $this->productService->list($request->all());
        return view('admin.products.index', compact('products'));
    }

    /**
     * Lưu sản phẩm mới vào Database
     */
    public function store(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active'   => 'boolean'
        ]);

        try {
            // 2. Gọi sang Service để xử lý lưu trữ
            $this->productService->create($validated);

            // 3. Trả về thông báo thành công
            return redirect()->route('admin.products.index')
                             ->with('success', 'Thêm sản phẩm thành công!');
        } catch (\Exception $e) {
            // Log lỗi nếu có vấn đề xảy ra
            Log::error("Lỗi thêm sản phẩm: " . $e->getMessage());
            
            return back()->withInput()
                         ->with('error', 'Có lỗi xảy ra, vui lòng thử lại.');
        }
    }
}