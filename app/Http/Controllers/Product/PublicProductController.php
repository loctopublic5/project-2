<?php

namespace App\Http\Controllers\Product;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Product\ProductService;
use App\Http\Resources\Product\ProductResource;

class PublicProductController extends Controller
{
    use ApiResponse;

    public function __construct(protected ProductService $productService) {}

    // GET /api/v1/products
    public function index(Request $request)
    {
        try{
        // 1. Lấy filters từ request
        $filters = $request->all();

        // 2. Gọi Service để lấy Paginator
        // Khi page vượt quá range (VD: page 100), Service trả về Paginator rỗng (items = [])
        $productsPaginator = $this->productService->list($filters);

        // 3. Transform dữ liệu bằng Resource
        // Laravel tự động xử lý collection rỗng tại đây
        $resource = ProductResource::collection($productsPaginator);

        // 4. Xác định message (Optional - làm màu cho đẹp)
        $message = $productsPaginator->isEmpty() 
            ? 'Không tìm thấy sản phẩm phù hợp.' 
            : 'Lấy danh sách sản phẩm thành công.';

        // 5. Trả về response chuẩn qua Trait
        // Trait sẽ tự bóc tách meta phân trang từ $resource
        return $this->success($resource, $message);
        }catch(Exception $e){
            return $this->error($e->getMessage());
        }
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