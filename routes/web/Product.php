<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Trang chủ - Quan trọng nhất để hiện sản phẩm ra Home
Route::get('/', [ProductController::class, 'home'])->name('home');

// 2. Danh sách sản phẩm
Route::get('/products', [ProductController::class, 'index'])->name('products');

// 3. Chi tiết sản phẩm (Dùng slug)
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// 4. Profile khách hàng
Route::get('/profile', function () {
    return view('customer.profile');
})->name('profile');

// 5. Liên hệ (Lưu ý: Bạn nên tạo ContactController riêng sau này, 
// hiện tại tôi trỏ tạm theo ý bạn nhưng hãy kiểm tra hàm show() trong ProductController)
Route::get('/contact', function () {
    return view('pages.contact'); // Giả định bạn có file contact.blade.php
})->name('contact');