<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddressApiTest extends TestCase
{
    
    use RefreshDatabase;
    public function test_user_can_create_new_address()
{
    $user = User::factory()->create();
    /** @var User $user */
    
    
    $payload = [
        'recipient_name' => 'Test User',
        'phone'          => '0987654321',
        'province_id'    => 1,
        'district_id'    => 1,
        'ward_id'        => 1,
        'address_detail' => '123 Street',
        'is_default'     => true
    ];

    // Hành động
    $response = $this->actingAs($user)->postJson('/api/v1/customer/addresses', $payload);

    
    // Kiểm tra kết quả
    $response->assertCreated(); // Check 201
    $response->assertJsonPath('data.recipient_name', 'Test User'); // Check JSON
    

    // Check DB xem đã lưu chưa
    $this->assertDatabaseHas('user_addresses', [
        'user_id' => $user->id,
        'phone'   => '0987654321',
        'is_default' => true
    ]);
}

public function test_setting_new_address_as_default_turns_off_old_default()
    {
        $user = User::factory()->create();
        
        // 1. Tạo địa chỉ cũ là default
        $oldAddress = \App\Models\UserAddress::factory()->create([
            'user_id' => $user->id,
            'is_default' => true
        ]);
    
        // 2. Gọi API tạo địa chỉ mới cũng là default
        $payload = [
            'recipient_name' => 'New Guy',
            'is_default'     => true, // <--- Cái ta muốn test
            
            // --- BỔ SUNG CÁC TRƯỜNG BẮT BUỘC (ĐỂ QUA CỬA VALIDATION) ---
            'phone'          => '0988888888',
            'province_id'    => 1,
            'district_id'    => 1,
            'ward_id'        => 1,
            'address_detail' => '456 New Street',
        ];
        /** @var User $user */
        $response = $this->actingAs($user)->postJson('/api/v1/customer/addresses', $payload);
    
        // Debug nếu cần: $response->dump();
        $response->assertCreated(); // Đảm bảo tạo thành công (201) trước đã

        // 3. Assert quan trọng: Cái cũ phải chuyển thành false
        $this->assertDatabaseHas('user_addresses', [
            'id' => $oldAddress->id,
            'is_default' => false 
        ]);
        
        // Cái mới phải là true
        $this->assertDatabaseHas('user_addresses', [
            'recipient_name' => 'New Guy',
            'is_default' => true
        ]);
    }

public function test_user_cannot_delete_others_address()
{
    $hacker = User::factory()->create();
    $victim = User::factory()->create();
    
    $victimAddress = \App\Models\UserAddress::factory()->create(['user_id' => $victim->id]);

    /** @var User $hacker */
    // Hacker cố xóa địa chỉ của Victim
    $response = $this->actingAs($hacker)->deleteJson("/api/v1/customer/addresses/{$victimAddress->id}");
    // Mong đợi lỗi (Thường Laravel trả về 404 nếu dùng findOrFail trong scope user)
    $response->assertStatus(400);

    // Kiểm tra DB: Dữ liệu vẫn còn đó
    $this->assertDatabaseHas('user_addresses', ['id' => $victimAddress->id]);
}
}
