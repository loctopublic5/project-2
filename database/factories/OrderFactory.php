<?php

namespace Database\Factories;


use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // User ID (Khóa ngoại để lấy thông tin user)
            'user_id' => \App\Models\User::factory(), 

            'code' => 'ORD-' . strtoupper($this->faker->unique()->bothify('??#####')),
            
            // XÓA DÒNG shipping_phone Ở ĐÂY ĐI NHÉ!
            // 'shipping_phone' => ... (Xóa)

            'shipping_address' => [
                'address' => $this->faker->address(),
                'city' => $this->faker->city(),
                // Nếu muốn lưu phone vào trong JSON địa chỉ thì thêm vào đây, còn không thì thôi
            ],

            'status' => $this->faker->randomElement(array_column(OrderStatus::cases(), 'value')),
            'payment_status' => $this->faker->randomElement(array_column(PaymentStatus::cases(), 'value')),
            'payment_method' => 'cod',
            
            'subtotal' => 0,
            'shipping_fee' => 30000,
            'total_amount' => 0,
            'note' => $this->faker->sentence(),
        ];
    }
}

