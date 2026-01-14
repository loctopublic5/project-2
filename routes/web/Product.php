<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;


    Route::get('/profile', function () {
        return view('customer.profile');
    })->name('profile');

    Route::get('/products', [ProductController::class, 'index'])->name('products');

    Route::get('contact', [ProductController::class, 'show'])->name('contact');