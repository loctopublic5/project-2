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
            // 2. Chuẩn bị Data cho Service
            $data = $request->except(['image']); // Lấy hết input trừ file image raw
            // Decode JSON string từ Frontend thành Array PHP để Model lưu đúng format
            if ($request->filled('attributes')) {
                $data['attributes'] = json_decode($request->input('attributes'), true);
            }
    
        
            // Chuẩn hóa 'image' đơn lẻ từ Request thành mảng 'images' cho Service
            if ($request->hasFile('image')) {
                $data['images'] = [$request->file('image')]; 
            }   

            $product = $this->productService->createProduct($data);

            return $this->success(new ProductResource($product), 'Tạo thành công sản phẩm !', 201);
        }catch(Exception $e){
            return $this->error($e->getMessage(), 500);
        }
    }

    public function update(SaveProductRequest $request, $id){
            try{
                $data = $request->validated();
                // 2. Chuẩn bị Data cho Service
                $data = $request->except(['image']); // Lấy hết input trừ file image raw
                // Decode JSON string từ Frontend thành Array PHP để Model lưu đúng format
                if ($request->filled('attributes')) {
                    $data['attributes'] = json_decode($request->input('attributes'), true);
                }

                // Chuẩn hóa 'image' đơn lẻ từ Request thành mảng 'images' cho Service
                    if ($request->hasFile('image')) {
                        $data['images'] = [$request->file('image')]; 
                    }   
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

    /**
     * 1. Lấy danh sách sản phẩm (Filter, Sort, Paginate)
     */
    public function index(Request $request)
    {
        try{
        // Lấy các tham số filter từ request
        $filters = $request->all();

        // Gọi Service xử lý query
        // LƯU Ý: Service của bạn đang dùng Product::active(). 
        // Admin cần thấy cả sản phẩm ẩn. Bạn nên sửa Service bỏ active() hoặc tạo hàm listForAdmin().
        $products = $this->productService->listAdmin($filters);

        // Trả về dữ liệu đã qua Resource format
        // Resource::collection sẽ tự động bọc trong key "data" và giữ nguyên meta pagination
        $data = ProductResource::collection($products);
        return $this->success($data, 'Lấy danh sách thành công');
        }catch(Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * 2. Lấy chi tiết sản phẩm (Để hiển thị form Edit)
     */
    public function show($id)
    {
        try {
            // Gọi Service lấy detail
            $product = $this->productService->getProductDetail($id);

            // Trả về 1 object resource
            return $this->success(new ProductResource($product),'Lấy thông tin sản phẩm thành công');

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}