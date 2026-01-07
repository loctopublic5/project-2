<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. LẤY ID CỦA CÁC ROLE RA TRƯỚC ---
        $adminRole = Role::where('slug', 'admin')->first();
        $customerRole = Role::where('slug', 'customer')->first();
        $warehouseRole = Role::where('slug', 'warehouse')->first();

        // Kiểm tra xem RoleSeeder đã chạy chưa
        if (!$adminRole || !$customerRole || !$warehouseRole) {
            $this->command->error('LỖI: Bạn chưa chạy RoleSeeder! Hãy chạy RoleSeeder trước.');
            return;
        }

        // --- 2. TẠO USER ADMIN ---
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'], // Điều kiện tìm (tránh trùng)
            [
                'full_name' => 'Admin',
                'password' => Hash::make('password123'), // Mật khẩu chung
                'phone' => '0909000001',
                'is_active' => true,
            ]
        );

        // Gắn quyền Admin (Thêm vào bảng user_roles)
        // syncWithoutDetaching: Đảm bảo không bị lỗi nếu chạy seeder nhiều lần
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);


        // --- 3. TẠO USER CUSTOMER ---
        $customerUser = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'full_name' => 'Khách Hàng Test',
                'password' => Hash::make('password123'),
                'phone' => '0909000002',
                'is_active' => true,
            ]
        );

    // Gắn quyền Customer
    $customerUser->roles()->syncWithoutDetaching([$customerRole->id]);

    // ====================================================
    // PHẦN 3: TẠO USER WAREHOUSE (Bổ sung cho UserSeeder của bạn)
    // ====================================================
    
    // Admin & Customer đã có ở UserSeeder kia rồi, ta chỉ tạo thêm ông Kho thôi
    $warehouseUser = User::firstOrCreate(
            ['email' => 'warehouse@example.com'],
            [
                'full_name' => 'Thủ Kho 01',
                'password'  => Hash::make('password123'),
                'phone'     => '0909999888',
                'is_active' => true,
            ]
        );

        // Gắn role warehouse cho user này
        $warehouseUser->roles()->syncWithoutDetaching([$warehouseRole->id]);
    }
}