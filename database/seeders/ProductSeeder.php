<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tắt kiểm tra khóa ngoại để xóa bảng an toàn
        Schema::disableForeignKeyConstraints();
        Product::truncate();
        Schema::enableForeignKeyConstraints();

        // 2. Lấy danh sách ID của Category đã có
        $categories = Category::pluck('id')->toArray();

        if (empty($categories)) {
            $this->command->warn('Chưa có category, hãy seed category trước (php artisan db:seed --class=CategorySeeder).');
            return;
        }

        // 3. Sử dụng withoutEvents để tăng tốc độ seed
        Product::withoutEvents(function () use ($categories) {

            for ($i = 1; $i <= 50; $i++) {
                $price = rand(100000, 10000000);
                // Tạo giá giảm ngẫu nhiên (60% - 90% giá gốc) hoặc không giảm
                $salePrice = rand(0, 1) 
                    ? rand((int)($price * 0.6), (int)($price * 0.9)) 
                    : null;

                Product::create([
                    'category_id' => $categories[array_rand($categories)],
                    'name'        => "Sản phẩm mẫu thể thao {$i}",
                    'slug'        => Str::slug("San pham mau the thao {$i}") . '-' . Str::random(5),
                    'sku'         => 'SKU-' . strtoupper(Str::random(8)),
                    'price'       => $price,
                    'sale_price'  => $salePrice,
                    'stock_qty'       => rand(0, 500), 
                    'description' => 'Đây là sản phẩm mẫu chất lượng cao dùng cho mục đích test hệ thống TMĐT.',
                    'attributes'  => json_encode([ // Encode JSON để đảm bảo an toàn dữ liệu
                        'size'  => ['S', 'M', 'L', 'XL'],
                        'color' => ['Đen', 'Trắng', 'Xanh', 'Đỏ'],
                    ]),
                    'view_count'  => rand(0, 1000),
                    'is_active'   => true,
                ]);
            }
        });

        $this->command->info('Đã seed thành công 50 sản phẩm mẫu!');
    }
}