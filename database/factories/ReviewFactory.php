<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Tạo quan hệ tự động nếu không truyền vào
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'order_id' => Order::factory(), 
            
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}