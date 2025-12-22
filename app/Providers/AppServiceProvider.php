<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
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
        // 1. Super Admin Bypass (Quyền lực tối thượng)
    // Nếu là Super Admin, cho qua mọi cửa mà không cần check kỹ.
    // Keyword: Gate::before(function ($user, $ability) { ... });
    // Logic: return $user->hasRole('super_admin') ? true : null;

    // 2. Dynamic Gate (Cầu nối)
    // Laravel không cho phép define dynamic gate trực tiếp dễ dàng như xưa.
    // Nhưng cách đơn giản nhất cho người mới là define các Gate cụ thể HOẶC
    // sử dụng Policy (sẽ học sau).
    
    // Tạm thời, để test tính năng ẩn hiện nút, hãy define cứng 1 cái Gate mẫu:
    Gate::define('products.create', function(User $user){
        return $user->hasPermissionTo('products', 'create');
    });

    }
}
