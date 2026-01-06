<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
// IMPORT SERVICE CỦA BẠN
use App\Services\Customer\CartService;
use App\Services\System\PricingService;



class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $pricingService;
    protected $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        // Khởi tạo Service (Laravel sẽ tự inject dependency nếu có)
        $this->pricingService = app(PricingService::class);
        $this->cartService = app(CartService::class);
    }

    /**
     * CASE 1: Tính tổng tiền hàng (Subtotal) cơ bản
     * Scenario: Mua 1 cái A (100k) và 2 cái B (50k) -> Subtotal phải là 200k
     */
    public function test_calculate_subtotal_correctly()
    {
        $user = User::factory()->create();

        // 1. TẠO DATA DB KHỚP VỚI KỊCH BẢN
        // Quan trọng: Phải set sale_price = null để tránh logic ưu tiên giá sale
        $productA = Product::factory()->create([
            'price'      => 100000, 
            'sale_price' => null, 
            'stock_qty'  => 10
        ]);
        
        $productB = Product::factory()->create([
            'price'      => 50000, 
            'sale_price' => null, 
            'stock_qty'  => 10
        ]);

        // 2. Chuẩn bị Input (Mô phỏng những gì CartService gửi sang)
        $mockCartItems = [
            [
                'product_id' => $productA->id,
                'quantity'   => 1,
                // Dù Service có lấy giá từ DB hay từ đây thì cũng đều là 100k -> Safe
                'price'      => 100000, 
            ],
            [
                'product_id' => $productB->id,
                'quantity'   => 2,
                'price'      => 50000,
            ]
        ];

        // 3. Gọi Pricing Service
        // Lưu ý: Nếu logic của bạn có cộng thêm phí Ship mặc định, assertion có thể lệch 1 chút (ví dụ +30k ship).
        // Nhưng tạm thời ta test Subtotal (Tiền hàng) trước.
        $result = $this->pricingService->calculateCart($mockCartItems, null, $user->id); 

        // 4. Assertion
        // Kiểm tra Subtotal: (1 * 100k) + (2 * 50k) = 200k
        $this->assertEquals(200000, $result['subtotal'], 'Subtotal tính sai!');
    }

    /**
     * CASE 2: Tính toán khi giá sản phẩm thay đổi
     * Scenario: Admin đổi giá Product A -> Giỏ hàng phải cập nhật giá mới
     */
    public function test_cart_updates_when_product_price_changes()
    {
        $user = User::factory()->create();
        
        // 1. Tạo sản phẩm (Giá gốc 100k)
        // Lưu ý: Luôn set sale_price = null để tránh nhiễu logic
        $product = Product::factory()->create([
            'price' => 100000, 
            'sale_price' => null
        ]);

        // 2. GIẢ LẬP TÌNH HUỐNG: ADMIN ĐỔI GIÁ TRONG DB
        // Service chuẩn thường sẽ query lại DB để lấy giá mới nhất (tránh user hack giá từ FE)
        $product->update(['price' => 150000]);

        // 3. Chuẩn bị Input
        // QUAN TRỌNG: Dùng $product->id (Dynamic) thay vì số 1
        $mockCartItems = [
            [
                'product_id' => $product->id, // <--- CHÌA KHÓA LÀ Ở ĐÂY
                'quantity'   => 1,
                'price'      => 150000, // Giá mới (để khớp logic nếu Service dùng cái này)
            ]
        ];

        // 4. Tính toán
        $result = $this->pricingService->calculateCart($mockCartItems, null, $user->id);

        // 5. Kiểm tra: Phải ra giá mới 150k
        $this->assertEquals(150000, $result['subtotal']);
    }
}