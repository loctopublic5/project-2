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
    return [
        'category_id' => Category::inRandomOrder()->first()->id ?? 1, // Lấy ngẫu nhiên 1 danh mục đã có
        'name'        => ucfirst($name),
        'slug'        => Str::slug($name) . '-' . Str::random(5),
        'price'       => $this->faker->numberBetween(100000, 2000000), // Giá từ 100k - 2tr
        'stock'       => $this->faker->numberBetween(10, 100),
        'description' => $this->faker->paragraph(),
        'is_active'   => true,
        'created_at'  => now(),
        'updated_at'  => now(),
    ];
    }
}
