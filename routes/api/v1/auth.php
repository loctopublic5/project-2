<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\AuthController;


Route::prefix('auth')->group(function(){

    // Đăng ký: POST /api/v1/auth/register
    Route::post('/register', [AuthController::class, 'register']);

    Route::middleware('throttle:5,1')->group(function(){
        // Đăng nhập: POST /api/v1/auth/login
    Route::post('/login', [AuthController::class, 'login']);
    });

    // Quên mật khẩu: POST /api/v1/auth/forgot-password
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

    //Đăt lại mật khẩu: POST /api/v1/auth/reset-password
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Đăng xuất: POST /api/v1/auth/logout
    Route::middleware('auth:sanctum')->group(function(){
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    


}); 
?>