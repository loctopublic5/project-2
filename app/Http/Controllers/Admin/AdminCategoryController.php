<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Product\CategoryService;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\Admin\AdminCategoryResource;

class AdminCategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $categories = $this->categoryService->getAll($request->all());
        
        // Nếu là Tree view (Collection) thì không dùng pagination response
        if ($request->get('type') === 'tree') {
            return response()->json([
                'status' => true,
                'message' => 'Lấy danh sách dạng cây thành công',
                'data' => AdminCategoryResource::collection($categories)
            ]);
        }

        // Mặc định trả về dạng phân trang
        return AdminCategoryResource::collection($categories)->additional([
            'status' => true,
            'message' => 'Lấy danh sách thành công'
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            $category = $this->categoryService->create($request->validated());
            
            return response()->json([
                'status' => true,
                'message' => 'Tạo danh mục thành công',
                'data' => new AdminCategoryResource($category)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Category $category)
    {
        return response()->json([
            'status' => true,
            'data' => new AdminCategoryResource($category->load('parent'))
        ]);
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            $updatedCategory = $this->categoryService->update($id, $request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật danh mục thành công',
                'data' => new AdminCategoryResource($updatedCategory)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi cập nhật: ' . $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $this->categoryService->delete($id);

            return response()->json([
                'status' => true,
                'message' => 'Xóa danh mục thành công'
            ]);
        } catch (Exception $e) {
            // Bắt lỗi logic từ Service (VD: đang có con)
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400); // Bad Request
        }
    }
}