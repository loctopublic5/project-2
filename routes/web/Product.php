<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/products', [ProductController::class, 'index'])
    ->name('products');

Route::get('/products/{slug}', [ProductController::class, 'show'])
    ->name('products.show');


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

Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');

