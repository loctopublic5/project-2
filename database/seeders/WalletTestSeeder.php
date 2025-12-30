<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WalletTestSeeder extends Seeder
{
    public function run()
    {
        $userId = 2; // Target User
        $mockOrderId = 9999;
        $mockProduct1Id = 999;
        $mockCategoryId = 99;
        $now = Carbon::now();

        $this->command->info('🧹 Cleaning up old test data for User ID: ' . $userId);

        // --- 1. CLEAN UP ---
        
        // A. Wallet & Transactions
        $userWallet = DB::table('user_wallets')->where('user_id', $userId)->first();
        if ($userWallet) {
            DB::table('wallet_transactions')->where('wallet_id', $userWallet->id)->delete();
            DB::table('user_wallets')->where('id', $userWallet->id)->delete();
        }

        // B. Order & Order Items
        DB::table('order_items')->where('order_id', $mockOrderId)->delete();
        DB::table('orders')->where('id', $mockOrderId)->delete();

        // C. Product & Category & Files
        DB::table('files')
            ->where('target_type', 'App\Models\Product')
            ->where('target_id', $mockProduct1Id)
            ->delete();

        DB::table('products')->where('id', $mockProduct1Id)->delete();
        DB::table('categories')->where('id', $mockCategoryId)->delete();

        $this->command->info('🚀 Seeding Mock Data based on ERD v3.3...');

        // --- 2. TẠO MOCK DATA ---
        
        // A. Category
        DB::table('categories')->insert([
            'id' => $mockCategoryId,
            'name' => 'Danh mục Test',
            'slug' => 'danh-muc-test',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // B. Product
        DB::table('products')->insert([
            'id' => $mockProduct1Id,
            'category_id' => $mockCategoryId,
            'name' => 'Sản phẩm Test Laravel',
            'slug' => 'san-pham-test-laravel',
            'sku'  => 'TEST-SKU-999',
            'price' => 100000,
            'stock_qty' => 100,
            'description' => 'Mô tả sản phẩm test',
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // C. Wallet
        DB::table('user_wallets')->insert([
            'user_id' => $userId,
            'balance' => 0, 
            'status' => 'active', 
            'updated_at' => $now,
        ]);

        // D. Order (Đã thêm subtotal)
        DB::table('orders')->insert([
            'id' => $mockOrderId,
            'code' => 'ORD-TEST-9999',
            'user_id' => $userId,
            
            'subtotal' => 100000,      // <--- FIX: Thêm subtotal (Tiền hàng trước thuế/phí)
            'tax' => 0,                // Default 0 nhưng cứ thêm cho rõ
            'shipping_fee' => 0, 
            'discount_amount' => 0,    // Default 0
            'total_amount' => 100000,  // Tổng tiền = Subtotal + Tax + Ship - Discount

            'payment_method' => 'wallet', 
            'payment_status' => 'unpaid', // Đã mở comment vì DB bạn đã có cột này
            'status' => 'pending', 
            'shipping_address' => json_encode(['address' => '123 Test Street']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        
        // E. Order Items
        DB::table('order_items')->insert([
            'order_id' => $mockOrderId,
            'product_id' => $mockProduct1Id,
            'product_name' => 'Sản phẩm Test Laravel',
            'price_at_purchase' => 100000,
            'quantity' => 1,
        ]);

        $this->command->info("✅ DONE! \nUser ID: $userId \nOrder: ORD-TEST-9999 \nSubtotal: 100,000");
    }
}