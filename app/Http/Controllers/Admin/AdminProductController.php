<?php

namespace App\Http\Controllers\Admin; 

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Product\ProductService;
use App\Http\Resources\Product\ProductResource;
use App\Http\Requests\Product\SaveProductRequest;

class AdminProductController extends Controller
{
    use ApiResponse;
    protected $productService;

    // Sử dụng Dependency Injection để gọi Service
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    // Lưu ý: Dùng chung SaveProductRequest cho cả Store và Update
    
    public function store(SaveProductRequest $request){
        try{
            $data = $request->validated();

            $product = $this->productService->createProduct($data);

            return $this->success(new ProductResource($product), 'Tạo thành công sản phẩm !', 201);
        }catch(Exception $e){
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(SaveProductRequest $request, $id){
            try{
                $data = $request->validated();
                $product = $this->productService->updateProduct($id, $data);
                return $this->success(new ProductResource($product), 'Cập nhật thông tin sản phẩm thành công !');
            }catch(Exception $e){
                return $this->error($e->getMessage(), 500);
            }
    }

    public function destroy($id){
        try {
            $this->productService->deleteProduct($id);
            return $this->success(null, 'Xóa sản phẩm thành công');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}