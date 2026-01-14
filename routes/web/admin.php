<?php

use Illuminate\Support\Facades\Route;

// Định nghĩa các Route trả về VIEW cho Admin tại đây
// Lưu ý: Middleware 'web' đã được Laravel tự động áp dụng
Route::prefix('admin')->group(function () {
    
    // Trang Dashboard (Chỉ trả về cái vỏ HTML)
    Route::get('/dashboard', function () {
        return view('admin.dashboard.index');
    })->name('admin.dashboard.view');

});