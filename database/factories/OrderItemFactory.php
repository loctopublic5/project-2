<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'order_id' => \App\Models\Order::factory(),
        'product_id' => \App\Models\Product::factory(),
        
        'quantity' => $this->faker->numberBetween(1, 5),
        'price' => $this->faker->numberBetween(10, 100) * 10000, // Giá chẵn (100k, 200k...)
        
        // Snapshot tên sản phẩm tại thời điểm mua
        'product_name' => $this->faker->words(3, true),
    ];
}
}
