<?php

namespace App\Http\Controllers\Product;

use Exception;
use App\Traits\apiResponse;
use App\Http\Controllers\Controller;
use App\Services\Product\CategoryService;
use App\Http\Resources\Product\CategoryResource;

class CategoryController extends Controller
{
    use apiResponse;

    public function __construct(protected CategoryService $categoryService){}

    public function index(){
        try{
            $result = $this->categoryService->getMenuTree();
            //Vì $result là một danh sách (List/Collection)
            $data = CategoryResource::collection($result);

            return $this->success( $data, 'Lấy danh mục thành công!');
        }catch(Exception $e){
            return $this->error($e->getMessage(),500);
        }
    }
}
