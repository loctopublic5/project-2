<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;// Đảm bảo bạn đã import Enum này
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();
        
        // 1. Setup dữ liệu nền
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'rating_avg' => 0, 
            'review_count' => 0
        ]);

        // 2. Tạo một đơn hàng chuẩn (Hoàn thành) chứa sản phẩm trên
        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed' // Hoặc OrderStatus::COMPLETED
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);
    }

    /**
     * CASE 1: HAPPY PATH (Luồng chuẩn) & OBSERVER CHECK
     * User mua hàng -> Review -> Hệ thống tính lại điểm TB Product
     */
    public function test_user_can_review_purchased_product_and_rating_updates()
    {
        // Action: User gửi review 5 sao
        $response = $this->actingAs($this->user)
                        ->postJson("/api/v1/customer/products/{$this->product->id}/reviews", [
                            'order_id' => $this->order->id,
                            'rating' => 5,
                            'comment' => 'Sản phẩm tuyệt vời!'
                        ]);
        // $response->dump();

        // Assert 1: API trả về 201 Created
        $response->assertStatus(201);
        $response->assertJsonPath('data.rating', 5);

        // Assert 2: Database có bản ghi Review
        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'rating' => 5
        ]);

        // Assert 3: CHECK OBSERVER (Quan trọng!)
        // Bảng Product phải tự động cập nhật rating_avg = 5.0
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'rating_avg' => 5.0,
            'review_count' => 1
        ]);
    }

    /**
     * CASE 2: OBSERVER AVG CALCULATION
     * Test tính trung bình: 1 review 5 sao + 1 review 3 sao = 4.0
     */
    public function test_observer_calculates_average_rating_correctly()
    {
        // Giả lập 1 user khác đã review 5 sao trước đó
        $user2 = User::factory()->create();
        $order2 = Order::factory()->create(['user_id' => $user2->id, 'status' => 'completed']);
        Review::create([
            'user_id' => $user2->id,
            'product_id' => $this->product->id,
            'order_id' => $order2->id,
            'rating' => 5,
            'is_active' => true
        ]);

        // User hiện tại review 3 sao
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/customer/products/{$this->product->id}/reviews", [
                'order_id' => $this->order->id,
                'rating' => 3,
                'comment' => 'Tạm được'
            ]);
        
        // 👇 THÊM DÒNG NÀY ĐỂ DEBUG
        // Nếu API lỗi, nó sẽ dừng ở đây và in ra lỗi cho bạn thấy ngay
        $response->assertStatus(201); 

        // Assert: (5 + 3) / 2 = 4.0
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'rating_avg' => 4.0,
            'review_count' => 2
        ]);
    }

    /**
     * CASE 3: VALIDATION - PENDING ORDER
     * Đơn chưa xong không được review
     */
    public function test_cannot_review_pending_order()
    {
        // Update đơn hàng về trạng thái Pending
        $this->order->update(['status' => 'pending']);

        $response = $this->actingAs($this->user)
                        ->postJson("/api/v1/customer/products/{$this->product->id}/reviews", [
                            'order_id' => $this->order->id,
                            'rating' => 5
                        ]);

        // Expect: 400 Bad Request kèm message lỗi logic
        $response->assertStatus(400); 
        // Vì Controller catch Exception trả về JSON, ta check message
        $response->assertJsonFragment(['message' => 'Chỉ những đơn hàng đã hoàn thành mới được phép đánh giá.']);
    }

    /**
     * CASE 4: VALIDATION - PRODUCT NOT IN ORDER
     * Mua cái Quần (Product A) nhưng review cái Áo (Product B)
     */
    public function test_cannot_review_product_not_in_order()
    {
        // Tạo một sản phẩm khác (Product B)
        $productB = Product::factory()->create();

        // Cố tình dùng order cũ (chỉ chứa Product A) để review Product B
        $response = $this->actingAs($this->user)
                        ->postJson("/api/v1/customer/products/{$productB->id}/reviews", [
                            'order_id' => $this->order->id,
                            'rating' => 5
                        ]);
        // $response->dump();

        $response->assertStatus(400);
        $response->assertJsonFragment([
            'message' => "Sản phẩm ID {$productB->id} không nằm trong đơn hàng này."
        ]);
    }

    /**
     * CASE 5: VALIDATION - DUPLICATE REVIEW
     * Không được spam 2 lần 1 đơn
     */
    public function test_cannot_review_same_order_twice()
    {
        // Lần 1: Thành công
        $response1 = $this->actingAs($this->user)
            ->postJson("/api/v1/customer/products/{$this->product->id}/reviews", [
                'order_id' => $this->order->id,
                'rating' => 5
            ]);

        
        $response1->assertStatus(201); 
        $response1->assertJsonFragment(['message' => 'Đánh giá sản phẩm thành công.']);

        // Lần 2: Gửi lại y chang (Mong đợi lỗi)
        $response2 = $this->actingAs($this->user)
            ->postJson("/api/v1/customer/products/{$this->product->id}/reviews", [
                'order_id' => $this->order->id,
                'rating' => 4
            ]);

        $response2->assertStatus(400);
        $response2->assertJsonFragment(['message' => 'Bạn đã đánh giá sản phẩm này trong đơn hàng này rồi.']);
    }


    /**
     * CASE 6: PUBLIC LIST (GET INDEX)
     * Ai cũng xem được danh sách review
     */
    public function test_public_can_get_review_list()
    {
        // Tạo sẵn 3 review trong DB
        Review::factory()->count(3)->create([
            'product_id' => $this->product->id,
            'is_active' => true
        ]);

        // Gọi API Public (Không cần actingAs)
        $response = $this->getJson("/api/v1/products/{$this->product->id}/reviews");
        $response->dump();

        $response->assertStatus(200);
        
        // Check cấu trúc JSON Resource Collection
        $response->assertJsonStructure([
            'data' => [
                '*' => [ // Kiểm tra từng phần tử trong mảng data
                    'id',
                    'rating',
                    'comment',
                    'user' => ['full_name', 'avatar_url'], // Check relationship user
                    'human_time'
                ]
            ],
            'meta', // Check pagination keys
            'links'
        ]);
    }
}