<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function(){

    // Đăng ký: POST /api/v1/auth/register
    Route::post('/register', [AuthController::class, 'register']);

    // Đăng nhập: POST /api/v1/auth/login
    Route::post('/login', [AuthController::class, 'login']);
}); 

?>