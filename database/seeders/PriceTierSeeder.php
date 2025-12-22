<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PriceTier;

class PriceTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            [
                'name' => 'Bronze',
                'slug' => 'bronze',
                'discount_percentage' => 5, // Giảm 5%
            ],
            [
                'name' => 'Silver',
                'slug' => 'silver',
                'discount_percentage' => 10, // Giảm 10%
            ],
            [
                'name' => 'Gold',
                'slug' => 'gold',
                'discount_percentage' => 20, // Giảm 20%
            ],
            [
                'name' => 'Platinum',
                'slug' => 'platinum',
                'discount_percentage' => 30, // Giảm 30% (VIP)
            ],
        ];

        foreach ($tiers as $tier) {
            // Dựa vào 'slug' để kiểm tra: Có rồi thì update, chưa có thì create
            PriceTier::updateOrCreate(
                ['slug' => $tier['slug']], 
                $tier
            );
        }
    }
}