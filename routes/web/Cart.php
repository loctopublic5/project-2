<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\CartController;
use App\Services\Customer\CartService;
use App\Http\Requests\Cart\AddToCartRequest;

/*
|--------------------------------------------------------------------------
| Public Cart Routes (Cho phép truy cập không cần đăng nhập)
|--------------------------------------------------------------------------
*/

// Route thêm vào giỏ hàng (Huy đã sửa trả về JSON - Rất chuẩn!)
Route::post('/cart/add', function (AddToCartRequest $request, CartService $cartService) {
    $cartService->addToCart(
        1, // Customer ID tạm thời (Huy nên dùng auth()->id() || session()->getId() sau này)
        $request->product_id,
        $request->quantity,
        $request->options ?? []
    );

    return response()->json([
        'status' => 'success',
        'message' => 'Đã thêm vào giỏ hàng thành công!'
    ]);
})->name('cart.add');

// HUY ĐƯA ROUTE NÀY RA NGOÀI MIDDLEWARE ĐỂ HẾT LỖI 401
// web.php
Route::get('/cart-data-json', function (CartService $cartService) {
    $result = $cartService->getCartDetail(1);
    $cart = $result['cart'];
    
    // Tự tính tổng tiền nếu PricingService đang lỗi
    $total = collect($cart->items)->sum(function($item) {
        return $item->quantity * ($item->product->sale_price ?? $item->product->price);
    });

    return response()->json([
        'items' => $cart->items ?? [],
        'total' => $total,
        'count' => count($cart->items ?? [])
    ]);
});

/*
|--------------------------------------------------------------------------
| Private Cart Routes (Yêu cầu đăng nhập)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {
    // Các thao tác quản lý sâu hơn trong trang Checkout/Cart page
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::put('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
});