<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\AuthController;
    // 3. Đăng ký
    Route::get('/register', function () {
        return view('admin.auth.register');
    })->name('register');

     // 1. Route Login (Trả về view đăng nhập)
    // Đặt tên là 'admin.login' để sau này redirect cho dễ
    Route::get('/login', function () {
        return view('admin.auth.login');
    })->name('login');

    // 4. Quên mật khẩu (Form nhập email)
    Route::get('/forgot-password', function () {
        return view('admin.auth.forgot-password');
    })->name('password.request');

    // 5. Đặt lại mật khẩu (Form nhập pass mới)
    // Route này sẽ nhận ?token=...&email=... từ URL
    Route::get('/reset-password', function () {
        return view('admin.auth.reset-password');
    })->name('password.reset');

    Route::middleware('auth:sanctum')->group(function(){
        // 2. Route Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });


