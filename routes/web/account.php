<?php

use Illuminate\Support\Facades\Route;

// ================= PROFILE =================
Route::get('/profile', function () {
    return view('customer.Loc(Checkout).pages.account.profile');
})->name('profile');