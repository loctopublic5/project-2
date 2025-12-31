<?php

namespace App\Services\Product;

use Exception;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Services\System\FileService;

class ProductService
{
    
    public function __construct(protected FileService $fileService){}

    public function list(array $filters){
        // 1. Khởi tạo Query với Scope Active
        $query = Product::active()->with('images'); // Eager Load ảnh để hiển thị ra list

        // 2. Filter theo Category
        if(isset($filters['category_id'])){
            $query->where('category_id', $filters['category_id']);
        }

        // 3. Filter theo Keyword (Tìm kiếm)
        if(isset($fillter['keyword'])){
            $keyword = $filters['keyword'];
            $query->where('name' , 'LIKE', "%{$keyword}%");
        }

        // 4. Filter theo Giá (Range)
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // 5. Sorting (Sắp xếp)
        // Mặc định là mới nhất
        $sortBy = $fillters['sort_by'] ?? 'latest';
        switch ($sortBy){
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        // 6. Pagination
        // Trả về Paginator Object
        return $query->paginate($filters['limit'] ?? 20);
    }

    public function getProductDetail($id){
        return Product::active()
            ->with(['images', 'category', 'reviews'])
            ->findOrFail($id);
    }

    public function createProduct($data){

        // 1. Validate Logic Nghiệp vụ
        // Nếu có giá sale, thì giá sale phải nhỏ hơn giá gốc
        if (isset($data['sale_price']) && $data['sale_price'] >= $data['price']) {
            throw new Exception('Giá khuyến mãi phải nhỏ hơn giá gốc.');
        }

        //2. Khởi tạo DB Transaction:
        DB::beginTransaction();
        try{
            // Bước A: Tạo Product trước 
            $product = Product::create($data);

            //Xử lý Upload Ảnh (Nếu trong $data có file):
            if(isset($data['images']) && is_array($data['images'])){
                foreach($data['images'] as $file){
                    //Gọi sang FileService
                    $this->fileService->upload(
                        $file,                 // File vật lý
                        Product::class,  // target_type: "App\Models\Product"
                        $product->id       
                    );
                }
            }
            DB::commit();
            return $product;

        }catch (Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function updateProduct($id, $data){
        // 1. Tìm sản phẩm:
        $product = Product::findOrFail($id);

        // 2. Validate Logic:
        if (isset($data['sale_price']) && $data['sale_price'] >= $data['price']) {
            throw new Exception('Giá khuyến mãi phải nhỏ hơn giá gốc.');
        }

        // 3. Transaction Start:
        DB::beginTransaction();
        try{
            // a. Update thông tin cơ bản:
            $product->update($data);

            // b. Xử lý ảnh (Logic Thay Thế):
            if (isset($data['images'])){
                    // Bước 1: Xóa sạch ảnh cũ
                    $oldFiles = $product->images;
                    foreach ($oldFiles as $file){
                    $this->fileService->delete($file); // Xóa vật lý + Xóa DB
                    }
                    // Bước 2: Upload ảnh mới (nếu mảng không rỗng)
                    foreach ($data['images'] as $newFile){
                        $this->fileService->upload(
                            $newFile, 
                            Product::class,
                            $product->id);
                    }
            }
            DB::commit();
            return $product->refresh(); // Lấy lại data mới nhất

        }catch (Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteProduct($id){
        // 1. Tìm sản phẩm:
        $product = Product::findOrFail($id);

        // 2. Thực hiện xóa mềm:
        $product->delete();
        
        return true;
    }


    public function restoreProduct($id){
        // 1. Tìm sản phẩm (bao gồm cả đã xóa):
        $product = Product::onlyTrashed()->findOrFail($id);

        // 2. Khôi phục:
        $product->restore();

        return true;
    }
}
