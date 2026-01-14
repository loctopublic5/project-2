<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $price = $this->faker->numberBetween(100000, 1000000); // Giá từ 100k đến 1 triệu
        $salePrice = $this->faker->boolean(70) ? $this->faker->numberBetween(10000, $price - 10000) : null; // 70% có sale_price, nhỏ hơn price

        return [
            'category_id' => Category::factory(),
            'name'        => ucfirst($name),
            'slug'        => Str::slug($name) . '-' . Str::random(5),
            'price'       => $price,
            'sale_price'  => $salePrice,
            'stock_qty'   => $this->faker->numberBetween(10, 1000),
            'description' => $this->faker->paragraph(),
            'is_active'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
