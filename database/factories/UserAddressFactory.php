<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserAddress>
 */
class UserAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
    return [
        // user_id thường sẽ được override khi gọi Factory trong test
        'user_id' => \App\Models\User::factory(), 
        
        'recipient_name' => $this->faker->name,
        'phone'          => $this->faker->phoneNumber,
        'province_id'    => $this->faker->numberBetween(1, 63),
        'district_id'    => $this->faker->numberBetween(1, 500),
        'ward_id'        => $this->faker->numberBetween(1, 1000),
        'address_detail' => $this->faker->streetAddress,
        'is_default'     => false, // Mặc định là false
    ];
    }
}
