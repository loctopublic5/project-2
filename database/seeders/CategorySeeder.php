<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema; 
use Illuminate\Support\Facades\DB;     

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tắt kiểm tra khóa ngoại để có thể xóa bảng
        Schema::disableForeignKeyConstraints();
        
        // 2. Xóa dữ liệu cũ
        Category::truncate();
        
        // 3. Bật lại kiểm tra khóa ngoại sau khi truncate
        Schema::enableForeignKeyConstraints();

        // Level 1
        $fashion = Category::create([
            'parent_id' => null,
            'name'      => 'Thời trang thể thao',
            'slug'      => 'thoi-trang-the-thao',
            'level'     => 1,
            'is_active' => true,
        ]);

        $equipment = Category::create([
            'parent_id' => null,
            'name'      => 'Dụng cụ thể thao',
            'slug'      => 'dung-cu-the-thao',
            'level'     => 1,
            'is_active' => true,
        ]);

        // Level 2
        $clothes = Category::create([
            'parent_id' => $fashion->id,
            'name'      => 'Quần áo thể thao',
            'slug'      => 'quan-ao-the-thao',
            'level'     => 2,
            'is_active' => true,
        ]);

        $shoes = Category::create([
            'parent_id' => $fashion->id,
            'name'      => 'Giày thể thao',
            'slug'      => 'giay-the-thao',
            'level'     => 2,
            'is_active' => true,
        ]);

        // Level 3
        Category::insert([
            [
                'parent_id' => $clothes->id,
                'name'      => 'Áo thể thao',
                'slug'      => 'ao-the-thao',
                'level'     => 3,
                'is_active' => true,
                'created_at' => now(), // insert nên thêm created_at thủ công
                'updated_at' => now(),
            ],
            [
                'parent_id' => $clothes->id,
                'name'      => 'Quần thể thao',
                'slug'      => 'quan-the-thao',
                'level'     => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parent_id' => $shoes->id,
                'name'      => 'Giày chạy bộ',
                'slug'      => 'giay-chay-bo',
                'level'     => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parent_id' => $shoes->id,
                'name'      => 'Giày bóng đá',
                'slug'      => 'giay-bong-da',
                'level'     => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}