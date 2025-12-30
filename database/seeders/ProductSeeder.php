<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id')->toArray();

        if (empty($categories)) {
            $this->command->warn('Chưa có category, hãy seed category trước.');
            return;
        }

        Product::withoutEvents(function () use ($categories) {

            for ($i = 1; $i <= 50; $i++) {

                $price = rand(100_000, 10_000_000);
                $salePrice = rand(0, 1) 
                    ? rand((int)($price * 0.6), (int)($price * 0.9)) 
                    : null;

                Product::create([
                    'category_id' => $categories[array_rand($categories)],
                    'name'        => "Sản phẩm mẫu {$i}",
                    'slug'        => Str::slug("San pham mau {$i}"),
                    'sku'         => 'SKU-' . strtoupper(Str::random(8)),
                    'price'       => $price,
                    'sale_price'  => $salePrice,
                    'stock_qty'   => rand(0, 500),
                    'description' => 'Đây là sản phẩm mẫu dùng cho mục đích test hệ thống.',
                    'attributes'  => [
                        'size'  => ['S', 'M', 'L', 'XL'],
                        'color' => ['Đen', 'Trắng', 'Xanh', 'Đỏ'],
                    ],
                    'view_count'  => rand(0, 1000),
                    'is_active'   => true,
                ]);
            }

        });
    }
}
