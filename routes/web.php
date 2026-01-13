<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;


/*
|--------------------------------------------------------------------------
| Web Routes Loader
|--------------------------------------------------------------------------
| Thay vì viết code ở đây, chúng ta load tự động từ folder routes/web/
*/

Route::group([], function () {
    // Quét tất cả file .php trong folder routes/web/
    foreach (glob(__DIR__ . '/web/*.php') as $filename) {
        require $filename;
    }
});

// 1. Trang chủ (Trả về view home chứ không phải layout app)
Route::get('/', function () {
    return view('pages.home'); 
})->name('home');





