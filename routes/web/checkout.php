<?php 

use Illuminate\Support\Facades\Route;


Route::get('/checkout', function(){
    return view('customer.Loc(Checkout).pages.checkout');
})->name('checkout');