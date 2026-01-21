<?php

namespace App\Services\Product;

use Exception;
use App\Models\File;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Services\System\FileService;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    
    public function __construct(protected FileService $fileService){}

    public function list(array $filters){
        // 1. Khởi tạo Query với Scope Active
        $query = Product::active()->with(['category']);

        // 2. Filter theo Category
        if(isset($filters['category_id'])){
            $query->where('category_id', $filters['category_id']);
        }

        // 3. Filter theo Keyword (Tìm kiếm)
        if(isset($filters['keyword'])){
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
        $sortBy = $filters['sort_by'] ?? 'latest';
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

    //List cho Admin
    public function listAdmin(array $filters){
    // 1. Khởi tạo Query
    // QUAN TRỌNG: Thêm withoutGlobalScopes() để Admin thấy được sản phẩm ẩn (nếu Model có cài Scope)
    $query = Product::withoutGlobalScopes()->with(['category']); 

    // 2. Filter Keyword (Gộp logic tìm kiếm vào 1 chỗ duy nhất)
    if (!empty($filters['keyword'])) {
        $keyword = $filters['keyword'];
        $query->where(function($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('sku', 'like', "%{$keyword}%");
        });
    }

    // 3. Filter theo Trạng thái (Quan trọng)
    // Kiểm tra kỹ cả chuỗi "0" và số 0
    if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
        $query->where('is_active', (int)$filters['is_active']);
    }

    // 4. Filter theo Category
    if(!empty($filters['category_id'])){
        $query->where('category_id', $filters['category_id']);
    }

    // (ĐÃ XÓA ĐOẠN FILTER KEYWORD BỊ LẶP Ở ĐÂY)

    // 5. Filter theo Giá
    if (!empty($filters['min_price'])) {
        $query->where('price', '>=', $filters['min_price']);
    }
    if (!empty($filters['max_price'])) {
        $query->where('price', '<=', $filters['max_price']);
    }

    // 6. Sorting
    $sortBy = $filters['sort_by'] ?? 'latest';
    switch ($sortBy){
        case 'price_asc':
            $query->orderBy('price', 'asc');
            break;
        case 'price_desc':
            $query->orderBy('price', 'desc');
            break;
        default:
            $query->latest(); // Tương đương orderBy('created_at', 'desc')
    }
    
    return $query->paginate($filters['limit'] ?? 20);
}

    public function getProductDetail($id){
        return Product::with(['images', 'category', 'reviews'])
                        ->findOrFail($id);
}

    // Hàm Helper để chuẩn hóa Attribute
    // Input:  [ ["name" => "Màu", "value" => "Đỏ, Xanh"], ... ]
    // Output: [ "Màu" => ["Đỏ", "Xanh"], ... ]
    private function formatAttributes(array $rawAttributes): array
    {
        $formatted = [];
        foreach ($rawAttributes as $item) {
            if (!empty($item['name']) && !empty($item['value'])) {
                // 1. Tách chuỗi "a,b,c" thành mảng ["a", "b", "c"]
                // 2. Trim bỏ khoảng trắng thừa
                $values = array_map('trim', explode(',', $item['value']));
                
                // 3. Gán vào key là tên thuộc tính
                // Kết quả: $formatted["Màu"] = ["Đỏ", "Xanh"]
                $formatted[$item['name']] = $values;
            }
        }
        return $formatted;
    }
    public function createProduct($data) {
    // 1. Validate Logic Nghiệp vụ
    if (isset($data['sale_price']) && $data['sale_price'] >= $data['price']) {
        throw new Exception('Giá khuyến mãi phải nhỏ hơn giá gốc.');
    }

    DB::beginTransaction();
    try {
        // Chuẩn hóa attributes nếu có
        if (isset($data['attributes']) && is_array($data['attributes'])) {
            $data['attributes'] = $this->formatAttributes($data['attributes']);
        }

        // 1. Xử lý upload Thumbnail trước để lấy path
            if (isset($data['image'])) {
                // Ta sử dụng logic upload nhưng chỉ lấy path lưu vào product
                // Thay vì dùng fileService->upload (tạo record File), ta có thể viết 1 hàm upload riêng 
                // hoặc lấy path từ fileService. Ở đây tôi sẽ tối ưu lưu path vào thumbnail.
                $fileName = $data['image']->hashName();
                $folderPath = 'uploads/products/thumbnails/' . date('Y/m');
                $data['thumbnail'] = Storage::disk('public')->putFileAs($folderPath, $data['image'], $fileName);
            }

            $product = Product::create($data);

            // 2. Xử lý Gallery (Lưu vào bảng files qua Polymorphic)
            if (isset($data['gallery']) && is_array($data['gallery'])) {
                foreach ($data['gallery'] as $file) {
                    $this->fileService->upload($file, Product::class, $product->id);
                }
            }

            DB::commit();
            return $product;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * UPDATE: Dọn dẹp ảnh cũ & Cập nhật ảnh mới
     */
    public function updateProduct($id, $data) 
    {
        $product = Product::findOrFail($id);

        DB::beginTransaction();
        try {
            if (isset($data['attributes'])) {
                $data['attributes'] = $this->formatAttributes($data['attributes']);
            }

            // 1. Cập nhật Thumbnail mới & Xóa file vật lý cũ
            if (isset($data['image'])) {
                // Xóa ảnh thumbnail cũ nếu tồn tại
                if ($product->thumbnail && Storage::disk('public')->exists($product->thumbnail)) {
                    Storage::disk('public')->delete($product->thumbnail);
                }

                $fileName = $data['image']->hashName();
                $folderPath = 'uploads/products/thumbnails/' . date('Y/m');
                $data['thumbnail'] = Storage::disk('public')->putFileAs($folderPath, $data['image'], $fileName);
            }

            $product->update($data);

            // 2. Xóa ảnh Gallery cũ dựa trên deleted_images (JS gửi về)
            if (!empty($data['deleted_images'])) {
                $deleteIds = json_decode($data['deleted_images'], true);
                if (is_array($deleteIds)) {
                    $filesToDelete = File::whereIn('id', $deleteIds)->get();
                    foreach ($filesToDelete as $fileRecord) {
                        $this->fileService->delete($fileRecord); // Hàm này của bạn đã có xóa vật lý
                    }
                }
            }

            // 3. Thêm ảnh Gallery mới
            if (isset($data['gallery']) && is_array($data['gallery'])) {
                foreach ($data['gallery'] as $file) {
                    $this->fileService->upload($file, Product::class, $product->id);
                }
            }

            DB::commit();
            return $product->refresh();
        } catch (Exception $e) {
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
