<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function(){

    // Đăng ký: POST /api/v1/auth/register
    Route::post('/register', [AuthController::class, 'register']);

    // Đăng nhập: POST /api/v1/auth/login
    Route::post('/login', [AuthController::class, 'login']);

// Route này yêu cầu:
// 1. Phải đăng nhập (auth:sanctum)
// 2. Phải có role là 'admin' (role:admin)
Route::middleware(['auth:sanctum', 'role:admin'])->get('/admin-only', function () {
    return response()->json(['message' => 'Chào Admin!']);
});


}); 
?>