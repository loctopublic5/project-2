<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    
    // 1. Route Login (Trả về view đăng nhập)
    // Đặt tên là 'admin.login' để sau này redirect cho dễ
    Route::get('/login', function () {
        return view('admin.auth.login');
    })->name('admin.login');

    // 2. Route Dashboard (Đã làm)
    Route::get('/dashboard', function () {
        return view('admin.dashboard.index');
    })->name('admin.dashboard.view');

});