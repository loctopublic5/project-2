<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;

Route::prefix('admin')->group(function () {
    // 2. Route Dashboard 
    Route::get('/dashboard', function () {
        return view('admin.dashboard.index');
    })->name('admin.dashboard.view');

    Route::get('/categories', function () {
        return view('admin.dashboard.category');
    })->name('admin.categories.view');

    // 1. Trang danh sách (Index)
    Route::get('/users', function(){
        return view('admin.users.index');
    })->name('admin.users.index');
    
    // 2. Trang chi tiết (Show/Profile 360)
    Route::get('/users/{id}', function($id){
        return view('admin.users.show', ['id' => $id]);
    })->name('admin.users.show');

    // 3. Trang sản phẩm (Index)
    Route::get('/products', function(){
        return view('admin.products.index');
    })->name('admin.products.index');

    // 4. Trang Vận đơn (index)
    Route::get('/orders', function(){
        return view('admin.orders.index');
    })->name('admin.orders.index');
});