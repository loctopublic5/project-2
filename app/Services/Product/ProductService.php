<?php

namespace App\Services\Product;

use Exception;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Services\System\FileService;

class ProductService
{
    
    public function __construct(protected FileService $fileService){}

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

    
}
