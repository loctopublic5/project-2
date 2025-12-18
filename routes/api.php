<?php

use Illuminate\Support\Facades\Route;

// Lệnh này giữ nguyên prefix /api mặc định của Laravel
// Ta thêm prefix /v1 để versioning (thành /api/v1/...)
Route::prefix('v1')->group(function () {
    
    // Đường dẫn đến folder chứa các file module con
    // Lưu ý: __DIR__ hiện tại đang là folder routes/
    $routeFiles = glob(__DIR__ . '/api/v1/*.php');

    if ($routeFiles) {
        foreach ($routeFiles as $file) {
            require $file;
        }
    }
});