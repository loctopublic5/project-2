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

// Chắc chắn rằng dòng này thay thế cho route '/' cũ
Route::get('/', function(){
    return view('customer.Huy( Product - Cart).pages.products-list');
})->name('home');





