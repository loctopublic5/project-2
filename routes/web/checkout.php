<?php 

use Illuminate\Support\Facades\Route;


Route::get('/checkout', function(){
    return view('customer.pages.checkout.index');
})->name('checkout');

Route::get('/deposit', function () {
    return view('customer.pages.checkout.deposit_fast'); // Một view riêng biệt hoàn toàn
})->name('deposit.fast');