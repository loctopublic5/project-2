<?php

use Illuminate\Support\Facades\Route;

// ================= PROFILE =================
Route::get('/profile', function () {
    return view('customer.pages.account.index');
})->name('profile');