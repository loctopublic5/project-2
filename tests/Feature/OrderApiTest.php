<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\UserWallet;
use App\Models\UserAddress;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected $endpoint = '/api/v1/customer/orders'; // Sửa lại đúng route prefix của bạn

    public function test_user_can_place_order_successfully_with_wallet()
    {
        // ===========================
        // 1. SETUP DỮ LIỆU (Giả lập)
        // ===========================
        $user = User::factory()->create();
        /** @var User $user */
        // A. User & Ví (Có 1 triệu)
        UserWallet::updateOrCreate(
            ['user_id' => $user->id], // Điều kiện tìm kiếm
            ['balance' => 1000000, 'status' => 'active'] // Dữ liệu update/create
        );

        // B. Địa chỉ giao hàng
        $address = UserAddress::factory()->create(['user_id' => $user->id]);

        // C. Sản phẩm (Giá 100k, Kho 10 cái)
        $product = Product::factory()->create([
            'price'      => 100000,
            'sale_price' => 0,    // <--- THÊM DÒNG NÀY: Để tránh random giá khuyến mãi
            'stock_qty'  => 10,
            'is_active'  => true
        ]);

        // D. Giỏ hàng (User chọn mua 2 cái)

        // [BƯỚC 1]: Tạo cái "Giỏ" trước (Parent)
        // Dùng firstOrCreate để an toàn (hoặc create luôn cũng được vì đây là user mới tinh)
        $cart = \App\Models\Cart::create([
            'user_id' => $user->id
        ]);

        // [BƯỚC 2]: Tạo "Món hàng" bỏ vào giỏ (Child)
        CartItem::create([
            'cart_id'    => $cart->id, // <--- QUAN TRỌNG: Phải link tới ID của giỏ hàng vừa tạo
            'product_id' => $product->id,
            'quantity'   => 2,
            'selected'   => true 
            // Lưu ý: Có thể bảng cart_items của bạn không cần lưu user_id nữa vì đã link qua cart_id
        ]);

        // ===========================
        // 2. ACTION (Bấm nút Mua)
        // ===========================
        
        $payload = [
            'address_id'     => $address->id,
            'payment_method' => 'wallet',
            'voucher_code'   => null,
            'note'           => 'Giao nhanh nha shop'
        ];

        /** @var User $user */
        $response = $this->actingAs($user)->postJson($this->endpoint, $payload);

        $response->dump();

        // ===========================
        // 3. ASSERTION (Kiểm tra kết quả)
        // ===========================

        // A. Kiểm tra API Response
        $response->assertCreated(); // 201 Created
        $response->assertJsonStructure(['data' => ['code', 'total', 'items']]);
        
        // B. Kiểm tra DATABASE - Bảng ORDERS (Master)
        $this->assertDatabaseHas('orders', [
            'user_id'        => $user->id,
            'payment_method' => 'wallet',
            'payment_status' => 'paid', // Vì trả qua ví nên phải là PAID
            'subtotal'       => 200000, // 2 * 100k
            'total_amount'   => 235000, // 200k + 35k ship
            'note'           => 'Giao nhanh nha shop'
        ]);

        // C. Kiểm tra DATABASE - Bảng ORDER_ITEMS (Snapshot)
        $this->assertDatabaseHas('order_items', [
            'product_id'        => $product->id,
            'quantity'          => 2,
            'price_at_purchase' => 100000, // Giá lúc mua được lưu cứng
            'product_name'      => $product->name // Tên lúc mua được lưu cứng
        ]);

        // D. Kiểm tra DATABASE - Bảng PRODUCTS (Trừ kho)
        $this->assertDatabaseHas('products', [
            'id'        => $product->id,
            'stock_qty' => 8 // Ban đầu 10, mua 2, còn 8 (LOGIC QUAN TRỌNG)
        ]);

        // E. Kiểm tra DATABASE - Bảng USER_WALLETS (Trừ tiền)
        $this->assertDatabaseHas('user_wallets', [
            'user_id' => $user->id,
            'balance' => 765000 // 1tr - 235k
        ]);
        
        // F. Kiểm tra DATABASE - Bảng CART_ITEMS (Dọn dẹp)
        // Item đó phải biến mất khỏi giỏ
        $this->assertDatabaseMissing('cart_items', [
            'user_id'    => $user->id,
            'product_id' => $product->id
        ]);
    }

    // /**
    //  * Test Case 2: Hết hàng thì không được bán
    //  */
    // public function test_cannot_order_if_out_of_stock()
    // {
    //     $user = User::factory()->create();
    //     UserWallet::create(['user_id' => $user->id, 'balance' => 1000000, 'status' => 'active']);
    //     $address = UserAddress::factory()->create(['user_id' => $user->id]);

    //     // Sản phẩm chỉ còn 1 cái
    //     $product = Product::factory()->create(['stock_qty' => 1, 'price' => 100000]);

    //     // Cố tình mua 2 cái
    //     CartItem::create([
    //         'user_id'    => $user->id,
    //         'product_id' => $product->id,
    //         'quantity'   => 2,
    //         'selected'   => true
    //     ]);

    //     $response = $this->actingAs($user)->postJson($this->endpoint, [
    //         'address_id'     => $address->id,
    //         'payment_method' => 'wallet'
    //     ]);

    //     // Mong đợi lỗi 400 (Bad Request)
    //     $response->assertStatus(400);
        
    //     // Tiền trong ví phải còn nguyên
    //     $this->assertDatabaseHas('user_wallets', ['balance' => 1000000]);
    //     // Kho vẫn còn 1
    //     $this->assertDatabaseHas('products', ['stock_qty' => 1]);
    // }
}