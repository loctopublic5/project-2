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

        $response->dump();

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
}