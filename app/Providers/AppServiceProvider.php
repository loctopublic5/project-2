<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Order;
use App\Models\Review;

// Import các Model cần theo dõi
use App\Models\Product;
use App\Observers\UserObserver;
// use App\Models\Order; // Sau này thêm vào đây

// Import Observer
use App\Observers\AuditObserver;
use App\Observers\ReviewObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Danh sách các Model cần gắn "Camera chạy bằng cơm" (AuditObserver)
     * * Để mảng ở đây cho dễ quản lý, sau này thêm Model mới chỉ cần điền vào đây.
     */
    protected $modelsToAudit =[
        User::class,
        Product::class,
        Order::class,
        
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // =================================================================
        // PHẦN 1: CẤU HÌNH AUDIT LOG (OBSERVER)
        // =================================================================
        
        // [SENIOR TRICK]: Global Switch (Cầu dao tổng)
        // Kiểm tra file config (nếu có) hoặc biến môi trường.
        // Mục đích: Khi chạy Unit Test hoặc Import dữ liệu lớn, ta có thể tắt log 
        // bằng cách set AUDIT_ENABLED=false trong file .env mà không cần sửa code.
        $isAuditEnabled = config('app.audit_enabled', true);
        
        if($isAuditEnabled){
            foreach($this->modelsToAudit as $modelClass){
                // Kiểm tra class có tồn tại không để tránh lỗi crash app
                if(class_exists($modelClass)){
                    $modelClass::observe(AuditObserver::class);
                }
            }
        }



        // =================================================================
        // PHẦN 2: CẤU HÌNH PHÂN QUYỀN (GATE) - BASE MVP
        // =================================================================

        // 1. Super Admin Bypass (Gate::before)
        // Mặc dù trong hasPermissionTo() bạn đã check super_admin rồi,
        // nhưng việc giữ Gate::before ở đây vẫn RẤT CẦN THIẾT.
        // Lý do: Gate::before sẽ áp dụng cho cả các Policy hoặc các Gate khác 
        // mà có thể bạn không dùng hàm hasPermissionTo().
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        // 2. Define Gate Cụ thể
        // Truyền đúng 2 tham số resource và action
        
        Gate::define('products.create', function (User $user) {
            // Mapping: Gate 'products.create' -> Resource 'products', Action 'create'
            return $user->hasPermissionTo('products', 'create'); 
        });

        Gate::define('products.edit', function (User $user) {
            return $user->hasPermissionTo('products', 'edit'); 
        });

        Gate::define('products.delete', function (User $user) {
            return $user->hasPermissionTo('products', 'delete'); 
        });
        
        // Mẹo: Sau này bạn có thể viết vòng lặp để tự động đăng ký Gate 
        // dựa trên danh sách permission trong DB để đỡ phải define thủ công từng dòng.


        // Khai báo tạo ví tự động khi tạo tài khoản
        User::observe(UserObserver::class);

        // Khai báo tự động cập nhật đánh giá cho sản phẩm khi có đánh giá mới
        Review::observe(ReviewObserver::class);
    }

}

