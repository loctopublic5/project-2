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
    $query = Product::withoutGlobalScopes() 
                    ->with(['category', 'images']); 

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
        return Product::active()
            ->with(['images', 'category', 'reviews'])
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

        // Bước A: Tạo Product trước
        $product = Product::create($data);

        // Bước B: Gom tất cả ảnh (Ảnh chính và Gallery)
        $allFiles = [];
        
        // Ưu tiên ảnh chính (để nó được upload trước, ID nhỏ hơn -> làm thumbnail)
        if (isset($data['image'])) {
            $allFiles[] = $data['image'];
        }
        
        // Thêm các ảnh gallery
        if (isset($data['gallery']) && is_array($data['gallery'])) {
            $allFiles = array_merge($allFiles, $data['gallery']);
        }

        // Bước C: Xử lý Upload hàng loạt
        foreach ($allFiles as $file) {
            $this->fileService->upload(
                $file,
                Product::class,
                $product->id
            );
        }

        DB::commit();
        return $product;

    } catch (Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

    public function updateProduct($id, $data) {
    $product = Product::findOrFail($id);

    if (isset($data['sale_price']) && $data['sale_price'] >= $data['price']) {
        throw new Exception('Giá khuyến mãi phải nhỏ hơn giá gốc.');
    }

    DB::beginTransaction();
    try {
        // a. Update thông tin text
        if (isset($data['attributes'])) {
            $data['attributes'] = $this->formatAttributes($data['attributes']);
        }
        $product->update($data);

        // b. Xóa ảnh cũ (Giữ nguyên logic của bạn)
        if (!empty($data['deleted_images'])) {
            $deleteIds = json_decode($data['deleted_images'], true);
            if (is_array($deleteIds)) {
                $product->images()->whereIn('id', $deleteIds)->delete(); 
                // Lưu ý: Nhớ thực hiện xóa file vật lý trong fileService->delete nếu cần
            }
        }

        // c. Xử lý Ảnh chính (Thumbnail) - CHỈ upload nếu có file mới
        if (isset($data['image'])) {
            $path = $this->fileService->upload($data['image']); // Upload trả về path
            $product->update(['thumbnail' => $path]); // Cập nhật trực tiếp thumbnail
        }

        // d. Xử lý Gallery - Upload vào bảng liên kết, KHÔNG ghi đè thumbnail
        if (isset($data['gallery']) && is_array($data['gallery'])) {
            foreach ($data['gallery'] as $file) {
                // Giả sử hàm uploadGallery này chỉ lưu vào bảng product_images
                $path = $this->fileService->upload($file); 
                $product->images()->create(['url' => $path]);
            }
        }

        // d. Tiến hành upload
        foreach ($newFiles as $file) {
            $this->fileService->upload(
                $file, 
                Product::class,
                $product->id
            );
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
