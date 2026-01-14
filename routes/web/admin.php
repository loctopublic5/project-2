<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    // 2. Route Dashboard 
    Route::get('/dashboard', function () {
        return view('admin.dashboard.index');
    })->name('admin.dashboard.view');

    Route::get('/categories', function () {
        return view('admin.dashboard.category');
    })->name('admin.categories.view');

});