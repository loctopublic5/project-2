<?php

namespace App\Http\Controllers\Product;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\apiResponse;
use App\Services\Product\ProductService;
use App\Http\Resources\Product\ProductResource;

class PublicProductController extends Controller
{
    use apiResponse;

    public function __construct(protected ProductService $productService) {}

    // GET /api/v1/products
    public function index(Request $request)
    {
        $filters = $request->all();
        $products = $this->productService->list($filters);
        
        return $this->success(ProductResource::collection($products));
    }

    // GET /api/v1/products/{id}
    public function show($id)
    {
        try {
            $product = $this->productService->getProductDetail($id);
            return $this->success(new ProductResource($product));
        } catch (Exception $e) {
            return $this->error('Sản phẩm không tồn tại', 404);
        }
    }
}