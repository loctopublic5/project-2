<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\UserWallet; 
use Database\Seeders\RoleSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminOrderFlowTest extends TestCase
{
    use RefreshDatabase; // Tự động reset DB sau mỗi bài test

    protected $admin;
    protected $warehouse;
    protected $customer;
    protected $product;

    /**
     * CHUẨN BỊ DỮ LIỆU MẪU (Chạy trước mỗi function test)
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Chạy Seeder để có Role & Permission chuẩn
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);

        // 2. Tạo User & Gán Role
        // Admin: Full quyền
        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $roleAdmin = Role::where('slug', 'admin')->first();
        $this->admin->roles()->attach($roleAdmin);

        // Warehouse: Chỉ quyền Ship
        $this->warehouse = User::factory()->create(['email' => 'kho@test.com']);
        $roleKho = Role::where('slug', 'warehouse')->first();
        $this->warehouse->roles()->attach($roleKho);

        // Customer: Khách thường
        $this->customer = User::factory()->create(['email' => 'khach@test.com']);
        
        // 3. Tạo Ví cho khách (Để test hoàn tiền)
        UserWallet::updateOrCreate(
            ['user_id' => $this->customer->id], // Điều kiện tìm
            ['balance' => 0]                    // Dữ liệu update/tạo
        );

        // 4. Tạo Sản phẩm (Để test trừ kho)
        // Tồn kho ban đầu: 100 cái
        $this->product = Product::factory()->create([
            'stock_qty' => 100, 
            'price' => 50000
        ]);
    }

    /**
     * CASE 1: HAPPY PATH (Duyệt đơn -> Trừ kho)
     */
    public function test_admin_can_approve_order_and_stock_is_deducted()
    {
        // 1. Setup: Đơn hàng Pending, mua 2 sản phẩm
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending'
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2 // Mua 2 cái
        ]);

        // 2. Action: Admin gọi API Confirm
        $response = $this->actingAs($this->admin)
                        ->patchJson("/api/v1/admin/orders/{$order->id}/status", [
                            'status' => 'confirmed'
                        ]);

        // $response->dump();
        // 3. Assert (Kiểm tra kết quả)
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Cập nhật trạng thái đơn hàng thành công.']);

        // Check DB Order: Đã đổi status chưa?
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed'
        ]);

        // Check DB Product: Kho có bị trừ không? (100 - 2 = 98)
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_qty' => 98 // <--- QUAN TRỌNG
        ]);
    }

    /**
     * CASE 2: PERMISSION (Thủ kho không được duyệt đơn)
     */
    public function test_warehouse_cannot_approve_order()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        // Ông kho cố tình gọi API confirm
        $response = $this->actingAs($this->warehouse)
                        ->patchJson("/api/v1/admin/orders/{$order->id}/status", [
                            'status' => 'confirmed'
                        ]);

        // Phải bị chặn 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * CASE 3: STATE MACHINE (Không được Ship nếu chưa Duyệt)
     */
    public function test_cannot_ship_pending_order()
    {
        // Đơn đang pending (chưa confirm)
        $order = Order::factory()->create(['status' => 'pending']);

        // Dù là Admin hay Kho cũng không được nhảy cóc
        $response = $this->actingAs($this->warehouse)
                        ->patchJson("/api/v1/admin/orders/{$order->id}/status", [
                            'status' => 'shipping'
                        ]);

        $response->dump();

        // Lỗi 422 (Logic Validation)
        $response->assertStatus(422); 
        $response->assertJsonValidationErrors(['status']);
    }

    /**
     * CASE 4: FULL FLOW: HỦY ĐƠN -> HOÀN KHO & HOÀN TIỀN
     */
    public function test_admin_cancel_refunds_money_and_restocks_inventory()
    {
        // 1. Setup: Đơn hàng ĐÃ DUYỆT (Confirmed), Đã thanh toán qua Ví
        // Lúc này kho đã bị trừ (còn 98), tiền đã trừ (giả sử).
        
        // Update kho về 98 (giả lập là đã bị trừ)
        $this->product->update(['stock_qty' => 98]);
        
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'confirmed', // Đã duyệt
            'payment_method' => 'wallet',
            'payment_status' => 'paid',
            'total_amount' => 100000, // 100k
            'code' => 'ORD-TEST-REFUND'
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        // Ví khách đang 0 đồng
        $this->assertDatabaseHas('user_wallets', ['user_id' => $this->customer->id, 'balance' => 0]);

        // 2. Action: Admin Hủy đơn
        $response = $this->actingAs($this->admin)
                         ->patchJson("/api/v1/admin/orders/{$order->id}/status", [
                             'status' => 'cancelled',
                             'reason' => 'Hết hàng đột xuất'
                         ]);

        $response->assertStatus(200);

        // 3. Assert Results
        
        // A. Check Kho: Phải được cộng lại (98 + 2 = 100)
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_qty' => 100 
        ]);

        // B. Check Tiền: Ví phải được cộng 100k (0 + 100000 = 100000)
        $this->assertDatabaseHas('user_wallets', [
            'user_id' => $this->customer->id,
            'balance' => 100000
        ]);

        // C. Check Lịch sử giao dịch Ví (Transaction Log)
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $this->customer->wallet->id,
            'type' => 'refund',
            'amount' => 100000,
            'reference_id' => 'ORD-TEST-REFUND'
        ]);

        // D. Check Order Status
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
            'payment_status' => 'refunded'
        ]);
    }
}