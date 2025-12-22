<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Danh sách 3 Roles cần tạo
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin', // Slug quan trọng để check code
                'description' => 'Quản trị viên hệ thống toàn quyền',
            ],
            [
                'name' => 'Dealer',
                'slug' => 'dealer',
                'description' => 'Đại lý bán hàng (Sau khi được duyệt)',
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Khách hàng vãng lai',
            ],
        ];

        foreach ($roles as $role) {
            // firstOrCreate: Tìm theo slug, nếu chưa có thì tạo mới
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
