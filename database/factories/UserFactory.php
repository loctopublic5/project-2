<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('0#########'),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => 1,
        ];
    }

    /**
 * State để tạo Admin User (Tự động gán role admin)
 */
public function admin(): static
{
    return $this->afterCreating(function (User $user) {
        // Tìm hoặc tạo role admin
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'admin'], // Điều kiện tìm
            ['slug' => 'admin']  // Giá trị gán thêm nếu phải tạo mới
        );
        // Gắn role vào user thông qua bảng trung gian
        $user->roles()->attach($role->id);
    });
}
}