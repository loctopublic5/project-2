<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes Loader
|--------------------------------------------------------------------------
| Thay vì viết code ở đây, chúng ta load tự động từ folder routes/web/
*/

Route::group([], function () {
    // Quét tất cả file .php trong folder routes/web/
    foreach (glob(__DIR__ . '/web/*.php') as $filename) {
        require $filename;
    }
});

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

