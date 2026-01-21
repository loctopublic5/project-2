<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| PRODUCT & SHOP ROUTES
|--------------------------------------------------------------------------
*/

// ================= HOME =================
Route::get('/', [ProductController::class, 'home'])
    ->name('home');


// ================= PRODUCT =================

// Danh sách sản phẩm
Route::get('/products', [ProductController::class, 'index'])
    ->name('products.list');

// Chi tiết sản phẩm (slug)
Route::get('/products/{slug}', [ProductController::class, 'show'])
    ->name('products.show');


// ================= CART =================
Route::get('/cart', function () {
    return view('customer.cart.index');
})->name('cart');


// ================= CHECKOUT =================
Route::get('/checkout', function () {
    return view('customer.checkout.index');
})->name('checkout');



// ================= STATIC PAGES =================
Route::get('/contact', function () {
    return view('customer.pages.contact');
})->name('contact');

Route::get('/about', function () {
    return view('customer.pages.about');
})->name('about');

Route::get('/wishlist', function () {
    return view('customer.pages.wishlist');
})->name('wishlist');
