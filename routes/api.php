<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\AuthController;
use App\Http\Controllers\System\FileController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\WalletController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Admin\AdminWalletController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Product\PublicProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Base URL mặc định: /api
|  URL chuẩn: /api/v1/...
*/
Route::prefix('v1')->group(function () {

    /* =================================================================
        1. AUTH MODULE
        URL: /api/v1/auth/...
    ================================================================= */
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
    
    /* =================================================================
        2. PUBLIC DATA (Ai cũng xem được)
        URL: /api/v1/...
    ================================================================= */
    // Get /api/v1/categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
    });
    Route::prefix('products')->group(function () {
        Route::get('/', [PublicProductController::class, 'index']);
        Route::get('/{id}', [PublicProductController::class, 'show']);
    });


    /* =================================================================
        3. CUSTOMER MODULE (Yêu cầu Login)
        URL: /api/v1/customer/...
    ================================================================= */
    Route::prefix('customer')->group(function(){
        // NHÓM 1: USER ROUTES (Khách hàng dùng)
        Route::prefix('wallet')->middleware('auth:sanctum')->group(function(){
            // GET /api/wallet/me -> Xem số dư & lịch sử
            Route::get('/', [WalletController::class, 'getMe']);
            // POST /api/wallet/deposit -> Nạp tiền (Auto-approve)
            Route::post('/deposit', [WalletController::class, 'deposit']);
        });

        Route::prefix('payment')->middleware('auth:sanctum')->group(function(){
            Route::post( '/',[PaymentController::class, 'payByWallet']);
        });

        // ADDRESS ROUTES
        Route::prefix('addresses')->middleware('auth:sanctum')->group(function(){
            Route::get('/', [AddressController::class, 'index']);
            Route::post('/', [AddressController::class, 'store']);
            Route::get('/{id}', [AddressController::class, 'show']);
            Route::put('/{id}', [AddressController::class, 'update']);
            Route::delete('/{id}', [AddressController::class, 'destroy']);
        
            // Route đặc biệt: Set default
            Route::patch('/{id}/default', [AddressController::class, 'setDefault']);
    });

        // CART ROUTES (Role: Customer)
        Route::prefix('cart')->middleware('auth:sanctum')->group(function(){
            Route::get('/', [CartController::class, 'index']);      
            Route::post('/', [CartController::class, 'store']);     
            Route::put('/{id}', [CartController::class, 'update']); 
            Route::delete('/{id}', [CartController::class, 'destroy']); 
            Route::delete('/', [CartController::class, 'clear']);
        });
    });
    
    /* =================================================================
        4. ADMIN MODULE (Role: Admin)
        URL: /api/v1/admin/...
    ================================================================= */
    Route::prefix('admin')->group(function(){
        Route::middleware(['auth:sanctum', 'role:admin'])->group(function(){
            // POST /api/admin/wallet/refund -> Hoàn tiền cho khách
            Route::post('/refund', [AdminWalletController::class, 'refund']);
        });
    
        // 2. ADMIN API (Dùng AdminProductController)
        Route::prefix('products')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
            Route::post('/', [AdminProductController::class, 'store']);
            /* Lách luật bằng kỹ thuật Method Spoofing:
            Client (Frontend/Postman): Vẫn gửi Request là POST (để PHP đọc được file).
            Body Data: Gửi kèm một field ẩn tên là _method với giá trị là PUT.
            */ 
            Route::put('/{id}', [AdminProductController::class, 'update']);
            Route::delete('/{id}', [AdminProductController::class, 'destroy']);
        });
    });

    /* =================================================================
        5. SYSTEM UTILITIES
    ================================================================= */
    Route::middleware(['auth:sanctum'])->group(function () {
            // POST api/upload
            Route::post('/upload', [FileController::class, 'store']);
        });
    
});


// Syntax cũ dành cho đọc folder api để gọi api theo cách làm việc tránh conflict trước kia khi cả 2 cùng làm backend
//-------------------------------------------------------------------
// Lệnh này giữ nguyên prefix /api mặc định của Laravel
// Ta thêm prefix /v1 để versioning (thành /api/v1/...)
// Route::prefix('v1')->group(function () {
    
//     // Đường dẫn đến folder chứa các file module con
//     // Lưu ý: __DIR__ hiện tại đang là folder routes/
//     $routeFiles = glob(__DIR__ . '/api/v1/*.php');

//     if ($routeFiles) {
//         foreach ($routeFiles as $file) {
//             // [DEBUG] Uncomment dòng dưới để xem nó load file nào
//             //dump($file); 
//             require $file;
//         }
//     }
// });