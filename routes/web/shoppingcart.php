<?php

use Illuminate\Support\Facades\Route;

Route::get('/cart', function () {
        return view('customer.pages.cart.index');
    })->name('cart');