<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function(){

    // Đăng ký: POST /api/v1/auth/register
    Route::post('/register', [AuthController::class, 'register']);

    // Đăng nhập: POST /api/v1/auth/login
    Route::post('/login', [AuthController::class, 'login']);

    // Quên mật khẩu: POST /api/v1/auth/forgot-password
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

    // Route::post('/forgot-password', function() {
    //     return response()->json(['message' => 'Route đã thông! Lỗi nằm ở Controller']);
    // });
    
    // Đăng xuất: POST /api/v1/auth/logout
    Route::middleware('auth:sanctum')->group(function(){
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    

// Route này yêu cầu:
// 1. Phải đăng nhập (auth:sanctum)
// 2. Phải có role là 'admin' (role:admin)
Route::middleware(['auth:sanctum', 'role:admin'])->get('/admin-only', function () {
    return response()->json(['message' => 'Chào Admin!']);
});


}); 
?>