<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Gọi lần lượt theo đúng thứ tự (Role phải có trước User)
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,

        ]);
    }
}

