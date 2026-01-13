<?php
namespace App\Services\Product;

use Exception;
use App\Models\Category;

class CategoryService{
    
    public function getMenuTree(){

        $recursiveLoad = function($query) use (&$recursiveLoad){
            $query  ->where('is_active', true)
                    ->with(['children' => $recursiveLoad]);
        };

        return Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => $recursiveLoad])
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Lấy danh sách (Hỗ trợ Tree view hoặc Flat list)
     */
    public function getAll($params = [])
    {
        $query = Category::query();

        // Nếu request muốn lấy dạng cây (chỉ lấy danh mục gốc, load quan hệ con)
        if (isset($params['type']) && $params['type'] === 'tree') {
            return $query->whereNull('parent_id')
                         ->with('childrenRecursive') // Eager load đệ quy
                            ->get();
        }

        // Mặc định: Trả về phân trang flat list, sắp xếp mới nhất
        return $query->with('parent')->latest()->paginate(10);
    }

    /**
     * Tạo mới danh mục
     */
    public function create(array $data)
    {
        // 1. Tính toán level
        $data['level'] = $this->calculateLevel($data['parent_id'] ?? null);

        // 2. Tạo category (Slug và Code sẽ do Trait trong Model tự xử lý)
        return Category::create($data);
    }

    /**
     * Cập nhật danh mục
     */
    public function update(Category $category, array $data)
    {
        // 1. Nếu có thay đổi cha, phải tính lại level
        if (isset($data['parent_id']) && $data['parent_id'] != $category->parent_id) {
            $data['level'] = $this->calculateLevel($data['parent_id']);
        }

        $category->update($data);
        return $category;
    }

    /**
     * Xóa danh mục (Có kiểm tra ràng buộc)
     */
    public function delete(Category $category)
    {
        // 1. Check logic: Không được xóa nếu đang có danh mục con
        if ($category->children()->exists()) {
            throw new Exception("Không thể xóa danh mục này vì đang chứa danh mục con.");
        }

        // 2. Check logic: Không được xóa nếu có sản phẩm (Optional - nếu có bảng products)
        // if ($category->products()->exists()) {
        //     throw new Exception("Không thể xóa danh mục đang có sản phẩm.");
        // }

        return $category->delete();
    }

    /**
     * Helper: Tính toán Level dựa trên Parent ID
     */
    private function calculateLevel($parentId)
    {
        if (empty($parentId)) {
            return 1; // Cấp cao nhất
        }

        $parent = Category::find($parentId);
        return $parent ? ($parent->level + 1) : 1;
    }
}