<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Cách dùng: withoutEvents(callback)
        // Trong phạm vi hàm callback này, AuditObserver sẽ bị "bịt mắt" (Vô hiệu hóa)
        Product::withoutEvents(function () {
        
        // Gọi lần lượt theo đúng thứ tự 
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            PriceTierSeeder::class,
        ]);
        // Tạo 1000 sản phẩm cực nhanh vì không phải ghi 1000 dòng log
        Product::factory(50)->create(); 
        // Ra khỏi hàm này, Observer tự động bật lại.
    });

    }
}

