<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. LẤY CÁC ROLE ĐÃ TẠO (Từ RoleSeeder của bạn)
        $adminRole = Role::where('slug', 'admin')->first();
        $warehouseRole = Role::where('slug', 'warehouse')->first();

        if (!$adminRole || !$warehouseRole) {
            $this->command->error('❌ LỖI: Chưa tìm thấy Role Admin hoặc Warehouse. Hãy chạy RoleSeeder trước!');
            return;
        }

        // 2. ĐỊNH NGHĨA & TẠO PERMISSIONS
        // Cấu trúc khớp với OrderService (resource + action)
        $permissionsList = [
            // Admin Actions
            ['resource' => 'orders', 'action' => 'approve', 'name' => 'Duyệt đơn hàng'],
            ['resource' => 'orders', 'action' => 'cancel',  'name' => 'Hủy đơn hàng'],
            ['resource' => 'orders', 'action' => 'complete','name' => 'Hoàn tất đơn (Force)'],
            
            // Warehouse Actions
            ['resource' => 'orders', 'action' => 'ship',    'name' => 'Xuất kho/Giao hàng'],
        ];

        $permMap = [];

        foreach ($permissionsList as $p) {
            $perm = Permission::updateOrCreate(
                ['resource' => $p['resource'], 'action' => $p['action']], // Tìm theo cặp này
                ['name' => $p['name']]
            );
            // Lưu ID lại để gán cho nhanh
            $permMap["{$p['resource']}.{$p['action']}"] = $perm->id;
        }

        $this->command->info('✅ Đã tạo xong Permissions.');

        // 3. GÁN QUYỀN CHO ROLE (Mapping)
        
        // 3.1 Admin: Được Duyệt, Hủy, Hoàn tất
        $adminRole->permissions()->syncWithoutDetaching([
            $permMap['orders.approve'],
            $permMap['orders.cancel'],
            $permMap['orders.complete'],
        ]);

        // 3.2 Warehouse: Chỉ được Ship
        $warehouseRole->permissions()->syncWithoutDetaching([
            $permMap['orders.ship'],
        ]);

        $this->command->info('✅ Đã phân quyền cho Admin và Warehouse.');
    }
}