<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\CartController;

/*
|--------------------------------------------------------------------------
| Cart routes (Web)
|--------------------------------------------------------------------------
*/
use App\Services\Customer\CartService;
use App\Http\Requests\Cart\AddToCartRequest;

Route::post('/cart/add', function (
    AddToCartRequest $request,
    CartService $cartService
) {
    $userId = auth()->id();

    $cartService->addToCart(
        $userId,
        $request->product_id,
        $request->quantity,
        $request->options ?? []
    );

    return redirect()->back()->with('success', 'Đã thêm vào giỏ hàng');
})->name('cart.add');


Route::middleware(['auth'])->group(function () {

    // Trang giỏ hàng
    Route::get('/cart', [CartController::class, 'index'])
        ->name('cart.index');

    // Add to cart
    Route::post('/cart', [CartController::class, 'store'])
        ->name('cart.store');

    // Update cart item
    Route::put('/cart/{id}', [CartController::class, 'update'])
        ->name('cart.update');

    // Remove cart item
    Route::delete('/cart/{id}', [CartController::class, 'destroy'])
        ->name('cart.destroy');

    // Clear cart
    Route::delete('/cart', [CartController::class, 'clear'])
        ->name('cart.clear');
});


