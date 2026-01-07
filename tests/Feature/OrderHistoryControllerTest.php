<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderHistoryControllerTest extends TestCase
{
    use RefreshDatabase;

    // QUAN TRỌNG: Sửa lại đường dẫn này nếu route của bạn khác (ví dụ: /api/v1/orders)
    protected $endpoint = '/api/v1/customer/orders'; 

    /**
     * Case 1: Data Isolation
     * Test xem User A có bị lộ đơn hàng của User B không.
     */
    public function test_user_can_list_their_orders()
    {
        // ===========================
        // 1. SETUP (ARRANGE)
        // ===========================
        
        // Tạo User A (Người sẽ login)
        $me = User::factory()->create();
        
        // Tạo User B (Người lạ)
        $stranger = User::factory()->create();

        // Tạo 3 đơn hàng cho User A
        Order::factory()->count(3)->create([
            'user_id' => $me->id,
            'created_at' => now()->subDays(1) // Đặt thời gian để test sort nếu cần
        ]);

        // Tạo 2 đơn hàng cho User B (Mồi nhử)
        Order::factory()->count(2)->create([
            'user_id' => $stranger->id
        ]);

        // Kiểm tra db thực tế phải có 5 đơn
        $this->assertDatabaseCount('orders', 5);

        // ===========================
        // 2. ACTION (ACT)
        // ===========================
        
        // Login bằng User A và gọi API
        /** @var User $me */
        $response = $this->actingAs($me)->getJson($this->endpoint);

        // $response->dump();

        // ===========================
        // 3. ASSERTION (ASSERT)
        // ===========================

        // 3.1 Check HTTP Status
        $response->assertStatus(200);

        // 3.2 Check số lượng: Phải là 3 (của mình), không phải 5 (tất cả)
        // Lưu ý: Nếu bạn dùng Resource Collection, dữ liệu thường nằm trong key 'data'
        $response->assertJsonCount(3, 'data');

        // 3.3 Check cấu trúc JSON (đảm bảo OrderResource hoạt động đúng)
        // Bạn có thể thêm/bớt field tùy theo Resource thực tế của bạn
        $response->assertJsonStructure([
            'data' => [
                '*' => [ // Dấu * đại diện cho mỗi item trong mảng
                    'id',
                    'status',
                    'payment_status',
                    'total_amount',
                    'created_at'
                ]
            ],
            // 'links', 'meta' // Bỏ comment nếu bạn có phân trang (pagination)
        ]);
        
        // 3.4 Check kỹ: ID trả về KHÔNG được chứa ID của đơn hàng User B
        // Lấy ID của đơn đầu tiên user B
        $strangerOrderId = Order::where('user_id', $stranger->id)->first()->id;
        
        // Assert rằng trong chuỗi JSON trả về KHÔNG có ID đó
        $response->assertJsonMissing(['id' => $strangerOrderId]);
    }

    /**
     * Case 2: Test Phân trang (Pagination)
     * Tạo 15 đơn -> Page 1 lấy 10 -> Page 2 lấy 5
     */
    public function test_user_can_paginate_orders()
    {
        // 1. SETUP
        $user = User::factory()->create();
        
        // Tạo 15 đơn hàng cho user này
        Order::factory()->count(15)->create([
            'user_id' => $user->id
        ]);

        /** @var User $user */
        // 2. ACT - Gọi Page 1
        $response1 = $this->actingAs($user)->getJson($this->endpoint . '?page=1');
        
        // 3. ASSERT Page 1
        $response1->assertStatus(200)
                ->assertJsonCount(10, 'data')
                 // SỬA DÒNG DƯỚI ĐÂY:
                ->assertJsonPath('meta.total_items', 15) // Đổi total -> total_items
                ->assertJsonPath('meta.current_page', 1);

        // $response1->dump();
        // 4. ACT - Gọi Page 2
        $response2 = $this->actingAs($user)->getJson($this->endpoint . '?page=2');
        // $response2->dump();
        // 5. ASSERT Page 2
        $response2->assertStatus(200)
                  ->assertJsonCount(5, 'data'); // 15 - 10 = 5
    }

    /**
     * Case 3: Test Lọc theo Trạng thái (Filter)
     * Tạo đơn Pending và Completed -> Lọc chỉ lấy Pending
     */
    public function test_user_can_filter_orders_by_status()
    {
        // 1. SETUP
        $user = User::factory()->create();

        // Tạo 3 đơn Pending (Chờ xử lý)
        Order::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'pending' 
        ]);

        // Tạo 2 đơn Completed (Đã xong) - Mồi nhử
        Order::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'completed'
        ]);

        // 2. ACT
        // Gọi API có tham số ?status=pending
        /** @var User $user */
        $response = $this->actingAs($user)
                        ->getJson($this->endpoint . '?status=pending');
        // $response->dump();

        // 3. ASSERT
        $response->assertStatus(200);
        
        // Phải trả về đúng 3 đơn pending
        $response->assertJsonCount(3, 'data');

        // Kiểm tra ngẫu nhiên 1 item xem status có đúng là pending không
        $this->assertEquals('pending', $response->json('data.0.status.key'));
    }

    /**
     * Case 4: Xem chi tiết đơn hàng (Happy Path)
     */
    public function test_user_can_view_order_details()
    {
        // 1. Setup
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        // 2. Act
        /** @var User $user */
        $response = $this->actingAs($user)->getJson($this->endpoint . '/' . $order->id);

        // 3. Assert
        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $order->id);
    }

    /**
     * Case 5: Bảo mật - Không xem được đơn của người khác (Security)
     */
    public function test_user_cannot_view_others_order()
    {
        // 1. Setup: User A (Hacker) và User B (Nạn nhân)
        $hacker = User::factory()->create();
        $victim = User::factory()->create();
        $orderOfVictim = Order::factory()->create(['user_id' => $victim->id]);

        // 2. Act: Hacker cố tình xem đơn của Victim
        /** @var User $hacker */
        $response = $this->actingAs($hacker)
                        ->getJson($this->endpoint . '/' . $orderOfVictim->id);
        $response->dump();

        // 3. Assert: Mong đợi lỗi 403 Forbidden
        // Nếu API trả về 404 (Not Found) cũng chấp nhận được (bảo mật qua việc giấu ID)
        // Nhưng chuẩn Policy Laravel thường là 403.
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    /**
     * Case 6: Xem đơn không tồn tại
     */
    public function test_user_cannot_view_non_existent_order()
    {
        $user = User::factory()->create();
        
        // ID 99999 không tồn tại
        /** @var User $user */
        $response = $this->actingAs($user)->getJson($this->endpoint . '/99999');
        $response->dump();

        $response->assertStatus(404);
    }
    /**
     * Case 7: Hủy đơn hàng thành công (Happy Path)
     * Điều kiện: Đơn đang 'pending' -> Chuyển sang 'cancelled'
     */
    public function test_user_can_cancel_pending_order()
    {
        // 1. SETUP
        $user = User::factory()->create();
        
        // Tạo đơn hàng đang chờ
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending' // Chỉ pending mới được hủy
        ]);

        // 2. ACT
        // Gọi API POST /api/orders/{id}/cancel
        /** @var User $user */
        $payload = ['reason' => 'Tôi đổi ý, không muốn mua nữa'];

        $response = $this->actingAs($user)
                        ->putJson($this->endpoint . '/' . $order->id . '/cancel', $payload);
        // $response->dump();
        // 3. ASSERT
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Hủy đơn thành công']);

        // Kiểm tra Database: Status phải đổi thành 'cancelled'
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled'
        ]);
    }

    /**
     * Case 8: Không được hủy đơn đã xử lý (Business Logic)
     * Điều kiện: Đơn đang 'shipping' hoặc 'completed' -> Báo lỗi 400
     */
    public function test_cannot_cancel_shipping_order()
    {
        // 1. SETUP
        $user = User::factory()->create();
        
        // Tạo đơn hàng đã đi giao
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'shipping' 
        ]);

        // 2. ACT
        /** @var User $user */
        $payload = ['reason' => 'Tôi đổi ý, không muốn mua nữa'];

        $response = $this->actingAs($user)
                        ->putJson($this->endpoint . '/' . $order->id . '/cancel', $payload);

        // $response->dump();
        // 3. ASSERT
        // Mong đợi lỗi 400 Bad Request
        $response->assertStatus(400); 
        
        // Database vẫn phải y nguyên, không được đổi
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipping'
        ]);
    }

    /**
     * Case 9: Không được hủy đơn của người khác (Security)
     */
    public function test_cannot_cancel_others_order()
    {
        $hacker = User::factory()->create();
        $victim = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $victim->id, 'status' => 'pending']);

        // Hacker cố hủy đơn của Victim
        /** @var User $hacker */
        $payload = ['reason' => 'Tôi đổi ý, không muốn mua nữa'];

        $response = $this->actingAs($hacker)
                        ->putJson($this->endpoint . '/' . $order->id . '/cancel', $payload);
        // $response->dump();

        // Mong đợi 404 (Không tìm thấy đơn để hủy) hoặc 403
        $this->assertTrue(
            in_array($response->status(), [400, 403, 404]), 
            'API phải trả về lỗi (400, 403 hoặc 404) khi user cố hủy đơn người khác.'
        );
    }
}