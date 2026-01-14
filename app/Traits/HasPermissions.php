<?php

namespace App\Traits;

use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait HasPermissions
{
    /**
     * Hàm 1: Kiểm tra User có giữ vai trò cụ thể nào không?
     * Ví dụ: $user->hasRole('super_admin')
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles->contains('slug',$roleSlug); 
    }

    /**
     * Hàm 2: Lấy tất cả quyền (Permissions) của User
     * Logic: User -> Roles -> Permissions
     */
    protected function getAllPermissions(): Collection
    {
        $cacheKey = 'user_permissions_' . $this->id;
        return Cache::remember($cacheKey, 3600, function () {

        // 1. Eager load để tránh N+1
        $this->load('roles.permissions');

        // 2. Tạo collection rỗng để chứa kết quả
        $allPermissions = collect();

        // 3. Loop và gom quyền
        foreach ($this->roles as $role) {
            foreach ($role->permissions as $permission) {
                // Đẩy từng permission vào danh sách chung
                $allPermissions->push($permission);
            }
        }

        // 4. Trả về và lọc trùng lặp theo ID (phòng trường hợp 1 User có 2 Role giống quyền nhau)
        return $allPermissions->unique('id');
    });
}

    /**
     * Hàm 3: Kiểm tra User có quyền làm việc X trên bảng Y không?
     * Ví dụ: $user->hasPermissionTo('products', 'create')
     * Dựa vào ERD: bảng permissions có cột 'resource' và 'action' 
     */
    public function hasPermissionTo(string $resource, string $action): bool
    {
        // Bước 1: Nếu user là Super Admin -> Luôn return true (Bỏ qua check)
        if ($this->hasRole('super_admin')) {
            return true;
        }

        $permissions = $this->getAllPermissions();
        return $permissions->contains(function($p) use($resource, $action){
            return $p->resource === $resource && $p->action === $action;
        });
    }
}