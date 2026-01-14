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
     * Cập nhật danh mục (Có check logic an toàn)
     */
    public function update($id, array $data)
{
    $category = Category::findOrFail($id);

    // 1. Logic an toàn: Nếu định ẨN danh mục (is_active = 0)
    if (isset($data['is_active']) && $data['is_active'] == 0) {
        if ($category->products()->exists()) {
            throw new Exception("Không thể ẩn danh mục này vì đang chứa sản phẩm. Vui lòng chuyển sản phẩm sang danh mục khác trước.");
        }
        if ($category->children()->where('is_active', 1)->exists()) {
            throw new Exception("Không thể ẩn danh mục này vì đang chứa danh mục con đang hoạt động.");
        }
    }

    // 2. Logic thay đổi CHA (parent_id)
    // Chỉ chạy logic này khi có gửi parent_id lên và giá trị đó khác với hiện tại
    if (isset($data['parent_id']) && $data['parent_id'] != $category->parent_id) {
        
        // Check A: Không được chọn chính mình làm cha (Cơ bản)
        if ($data['parent_id'] == $category->id) {
            throw new Exception("Danh mục cha không hợp lệ (không thể chọn chính mình).");
        }

        // --- CHECK B: LOGIC MỚI CỦA BẠN ---
        // Nếu danh mục này ĐANG CÓ CON, thì không được phép chọn ai làm cha nữa (phải đứng độc lập)
        // Trừ khi bạn set parent_id = null (trở về làm cha to nhất) thì được.
        if ($data['parent_id'] != null && $category->children()->exists()) {
            throw new Exception("Danh mục này đang có danh mục con, nên nó không thể trở thành danh mục con của người khác.");
        }

        // Logic tính lại level (nếu cần thiết cho hệ thống của bạn)
        $data['level'] = $this->calculateLevel($data['parent_id']);
    }

    $category->update($data);
    return $category;
}

    /**
     * Xóa danh mục (Có check logic an toàn)
     */
    public function delete($id)
    {
        $category = Category::findOrFail($id);

        // 1. Check: Đang có danh mục con?
        if ($category->children()->exists()) {
            throw new Exception("Không thể xóa: Danh mục này đang chứa các danh mục con.");
        }

        // 2. Check: Đang có sản phẩm?
        if ($category->products()->exists()) {
            // Đếm số lượng để báo lỗi chi tiết
            $count = $category->products()->count();
            throw new Exception("Không thể xóa: Đang có {$count} sản phẩm thuộc danh mục này.");
        }

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