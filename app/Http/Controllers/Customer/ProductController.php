<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    /**
     * Inject ProductService
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Danh sách sản phẩm (Trang shop)
     * GET /products
     */
    public function index(Request $request)
    {
        $products = $this->productService->list([
            'category_id' => $request->category_id,
            'keyword' => $request->keyword,
        ]);

        return view('customer.products.index', [
            'products' => $products
        ]);
    }

    /**
     * Chi tiết sản phẩm
     * GET /products/{slug}
     */
    public function show(string $slug)
    {
        $product = $this->productService->findBySlug($slug);

        abort_if(!$product, 404);

        return view('customer.products.show', [
            'product' => $product
        ]);
    }
}
