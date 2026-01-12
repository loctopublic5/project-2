<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\ProductController;

// 1. Trang chủ (Trả về view home chứ không phải layout app)
Route::get('/', function () {
    return view('pages.home'); 
});

// 2. Trang danh sách sản phẩm (Sửa lỗi 404 khi ấn "Sản phẩm")
Route::get('/products', function () {
    return view('pages.products');
})->name('products');

// 3. Các trang Auth (Sửa lỗi 404 khi ấn Đăng nhập/Đăng ký)
Route::get('/login', function () {
    return view('pages.auth.login');
})->name('login');

Route::get('/register', function () {
    return view('pages.auth.register');
})->name('register');

// 4. Trang Profile (Để không bị lỗi khi ấn vào ví tiền)
Route::get('/profile', function () {
    return view('pages.auth.profile');
})->name('profile');



// 5. Trang Chi admin
Route::get('/admin/test', function () {
    return view('layouts.admin');
});
// ADMIN ROUTES GROUP
// Middleware 'auth' để đảm bảo đã login thì mới check được role
// Tạm thời comment middleware lại nếu bạn chưa setup Login, để test giao diện trước.
Route::prefix('admin')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard.index'); // Chúng ta sẽ tạo file này ở Step 4
    })->name('admin.dashboard');

    // Các route giả lập để test Active Menu
    Route::get('/products', function(){ return "Trang Sản Phẩm"; });
    Route::get('/categories', function(){ return "Trang Danh Mục"; });
    Route::get('/orders', function(){ return "Trang Duyệt Đơn"; });
    Route::get('/shipments', function(){ return "Trang Vận Đơn"; });
});