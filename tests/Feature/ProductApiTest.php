<?php

namespace Tests\Feature;

use api;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Product; // Nhớ import Model
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductApiTest extends TestCase
{
    // Trait này giúp reset database sạch sẽ sau mỗi lần chạy test
    use RefreshDatabase; 

    /**
     * TC-01: Kiểm tra lấy danh sách mặc định thành công
     */
    public function test_can_get_product_list_default()
    {
        // 1. Chuẩn bị: Tạo giả 20 sản phẩm trong DB ảo
        Product::factory()->count(20)->create();

        // 2. Hành động: Gọi API
        $response = $this->getJson('/api/v1/products');

        // 3. Kiểm tra (Assert):
        $response->assertStatus(200) // Mong đợi HTTP 200
                 ->assertJsonStructure([ // Mong đợi cấu trúc JSON trả về
                    'status',
                    'message',
                    'data' => [
                         '*' => ['id', 'info', 'pricing'] // Kiểm tra item bên trong có các field này
                    ],
                    'meta' => ['current_page', 'total_items']
                ]);
    }

    /**
     * TC-04: Kiểm tra trường hợp Data rỗng (Page quá lớn)
     * Đây chính là cái bạn vừa fix xong
     */
    public function test_returns_empty_data_when_page_out_of_range()
    {
        // 1. Tạo 10 sản phẩm
        Product::factory()->count(10)->create();

        // 2. Gọi trang 100 (vượt quá)
        $response = $this->getJson('/api/v1/products?page=100');

        // 3. Kiểm tra
        $response->assertStatus(200)
                ->assertJson([
                    'status' => true,
                     'message' => 'Không tìm thấy sản phẩm phù hợp.', // Check đúng câu message bạn viết
                     'data' => [], // Data phải là mảng rỗng
                ]);
    }

    /**
     * TC-06: Kiểm tra tính năng Tìm kiếm (Filter)
     */
    public function test_can_filter_products_by_keyword()
    {
        // 1. Tạo 1 sản phẩm tên "iPhone 15"
        Product::factory()->create(['name' => 'iPhone 15 Pro Max']);
        // Tạo thêm 1 sản phẩm khác tên "Samsung" để gây nhiễu
        Product::factory()->create(['name' => 'Samsung Galaxy']);

        // 2. Gọi API search chữ "iPhone"
        $response = $this->getJson('/api/v1/products?keyword=iPhone');

        // 3. Kiểm tra
        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'iPhone 15 Pro Max']) // Phải tìm thấy iPhone
                 ->assertJsonMissing(['name' => 'Samsung Galaxy']);    // Không được thấy Samsung
    }

    /**
     * TC-14: Kiểm tra không hiển thị sản phẩm Inactive
     */
    public function test_do_not_show_inactive_products()
    {
        // Tạo 1 SP active và 1 SP inactive
        Product::factory()->create(['name' => 'Active Product', 'is_active' => 1]);
        Product::factory()->create(['name' => 'Inactive Product', 'is_active' => 0]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
                ->assertJsonFragment(['name' => 'Active Product'])
                ->assertJsonMissing(['name' => 'Inactive Product']);
    }

    /**
     * TC-19: Xem chi tiết sản phẩm thành công
     */
    public function test_can_get_product_detail()
    {
        // 1. Tạo 1 sản phẩm
        $product = Product::factory()->create();

        // 2. Gọi API chi tiết (Lưu ý prefix v1)
        $response = $this->getJson("/api/v1/products/{$product->id}");

        // 3. Kiểm tra
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'data' => [
                         'id' => $product->id,
                         'info' => [
                             'name' => $product->name, // Kiểm tra tên khớp
                         ]
                     ]
                 ]);
    }

    /**
     * TC-20: Xem chi tiết sản phẩm không tồn tại (404)
     */
    public function test_return_404_when_product_not_found()
    {
        // Gọi ID tào lao (999999)
        $response = $this->getJson("/api/v1/products/999999");

        // Laravel mặc định trả về 404 nếu dùng Route Model Binding hoặc findOrFail
        $response->assertStatus(404); 
    }

    /**
     * TC-21: Thêm mới sản phẩm (Quyền Admin)
     */
    // Thêm dòng chú thích này để Editor hiểu đây là User model, không phải Collection
        
    public function test_admin_can_create_new_product()
    {
        // 1. Tạo user Admin giả
        /** @var \App\Models\User $admin */
        $admin = User::factory()->admin()->create();

        // 2. Data
        $category = Category::factory()->create();
        $payload = [
            'name'        => 'Admin Product',
            'sku'         => 'ADM-001',
            'category_id' => $category->id,
            'price'       => 500000,
            'stock_qty'   => 10,
            'is_active'   => 1
        ];

        // 3. Gọi API với actingAs (Giả lập đã login)
        // Lưu ý URL đã đổi thành /api/v1/admin/products
        $response = $this->actingAs($admin)
                         ->postJson('/api/v1/admin/products', $payload);

        // 4. Assert
        $response->assertStatus(201); // Hoặc 200
        $this->assertDatabaseHas('products', ['sku' => 'ADM-001']);
    }

    /**
     * TC-23: Cập nhật sản phẩm (Dùng Method Spoofing: POST + _method=PUT)
     */
    public function test_admin_can_update_product()
    {
        // 1. Tạo Admin và Sản phẩm cũ
        /** @var \App\Models\User $admin */
        $admin = User::factory()->admin()->create();
        
        $product = Product::factory()->create([
            'name'      => 'Old Name',
            'price'     => 100000,
            'stock_qty' => 50
        ]);

        // 2. Chuẩn bị dữ liệu (Phải gửi ĐẦY ĐỦ các trường required trong SaveProductRequest)
        $payload = [
            '_method'     => 'PUT', // Method Spoofing
            
            // Các trường bắt buộc (Required)
            'category_id' => $product->category_id, // Giữ nguyên danh mục cũ
            'name'        => 'Updated Name',        // Đổi tên
            'price'       => 200000,                // Đổi giá
            'stock_qty'   => 10,                    // Đổi tồn kho
            
            // Các trường Nullable (Có thể bỏ qua hoặc gửi)
            'is_active'   => 1,
            'description' => 'Mô tả mới',
            
            // SKU/Slug nếu không gửi thì giữ nguyên hoặc sinh mới tùy logic Controller
            // Nhưng nếu gửi thì phải check unique (Request của bạn đã handle việc này rồi)
             'sku'         => $product->sku, 
        ];

        // 3. Gọi API
        $response = $this->actingAs($admin)
                         ->postJson("/api/v1/admin/products/{$product->id}", $payload);

        // 4. Assert
        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id'    => $product->id,
            'name'  => 'Updated Name',
            'price' => 200000,
            'stock_qty' => 10
        ]);
    }

    /**
     * TC-24: Xóa sản phẩm (Quyền Admin)
     */
    public function test_admin_can_delete_product()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)
                        ->deleteJson("/api/v1/admin/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    /**
     * TC-25 (MỚI): User thường không được phép thêm sửa xóa (Bảo mật)
     */
    public function test_normal_user_cannot_access_admin_routes()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(); 

        // Debug nhanh: In ra xem user này có role gì (nếu cần)
        //dd($user->roles->toArray());
        
        // Cố tình gọi API admin
        $response = $this->actingAs($user)
                        ->postJson('/api/v1/admin/products', []);

        // Mong đợi lỗi 403 Forbidden
        $response->assertStatus(403);
    }
    
    /**
     * TC-26 (MỚI): Khách (Chưa login) không được truy cập
     */
    public function test_guest_cannot_access_admin_routes()
    {
        // Không dùng actingAs
        $response = $this->postJson('/api/v1/admin/products', []);

        // Mong đợi lỗi 401 Unauthorized
        $response->assertStatus(401); 
    }
}
