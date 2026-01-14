<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartApiTest extends TestCase
{
    
    use RefreshDatabase;

    // Route API thêm vào giỏ 
    protected $endpoint = '/api/v1/customer/cart'; 

    /**
     * CASE 1: Thêm mới sản phẩm vào giỏ (Happy Path)
     */
    public function test_user_can_add_new_item_to_cart()
    {
        // 1. Setup: User và Product có hàng
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock_qty' => 10,
            'price'     => 100000,
            'is_active' => true
        ]);

        $payload = [
            'product_id' => $product->id,
            'quantity'   => 2
        ];

        /** @var User $user */
        // 2. Action
        $response = $this->actingAs($user)->postJson($this->endpoint, $payload);

        // 3. Assertion
        // Tôi giả định cấu trúc: bảng 'carts' (user_id) -> bảng 'cart_items' (cart_id, product_id, quantity)
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity'   => 2
        ]);
    }

    /**
     * CASE 2: Logic cộng dồn số lượng (Merge Quantity)
     * Đây là logic quan trọng nhất: Không được tạo 2 dòng cho cùng 1 sản phẩm
     */
    public function test_adding_same_product_increases_quantity_instead_of_new_row()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_qty' => 50]);

        /** @var User $user */
        // Lần 1: Thêm 2 cái
        $this->actingAs($user)->postJson($this->endpoint, [
            'product_id' => $product->id,
            'quantity'   => 2
        ]);

        // Lần 2: Thêm tiếp 3 cái nữa
        $this->actingAs($user)->postJson($this->endpoint, [
            'product_id' => $product->id,
            'quantity'   => 3
        ]);


        // Assertion:
        // Tổng số lượng trong DB phải là 5 (2 + 3)
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity'   => 5 
        ]);

        // Kiểm tra chỉ có duy nhất 1 dòng record cho sản phẩm này trong giỏ của user
        // (Bạn cần query vào bảng cart_items để đếm)
        // Cách đơn giản nhất để test unique là count tổng số record trong bảng cart_items (mong đợi là 1)
        $this->assertDatabaseCount('cart_items', 1); 
    }

    /**
     * CASE 3: Validation Tồn kho (Stock Check)
     */
    public function test_cannot_add_more_than_stock_quantity()
    {
        $user = User::factory()->create();
        
        // Tạo sản phẩm chỉ còn 5 cái
        $product = Product::factory()->create(['stock_qty' => 5]);

        /** @var User $user */
        // Cố tình mua 6 cái
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'product_id' => $product->id,
            'quantity'   => 6
        ]);

        // 1. Chấp nhận lỗi 400 (Do Service trả về)
        $response->assertStatus(400); 
        
        // 2. Kiểm tra xem có đúng là lỗi do Tồn kho không? 
        // (Tránh trường hợp 400 do lỗi khác)
        $response->assertJsonFragment([
            'status' => false
        ]);
        
        // Kiểm tra thông báo chứa từ khóa quan trọng (tùy message của bạn)
        // Ví dụ: check xem message có chứa chữ "chỉ còn" không
        $content = $response->json();
        $this->assertStringContainsString('chỉ còn', $content['message']);
    }
}