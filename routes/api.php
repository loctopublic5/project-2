<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\System\AuthController;
use App\Http\Controllers\System\FileController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Customer\WalletController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Admin\AdminWalletController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Order\OrderHistoryController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\System\NotificationController;
use App\Http\Controllers\Product\ProductReviewController;
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
        // Xem danh sách review của sản phẩm
        Route::get('/{id}/reviews', [ProductReviewController::class, 'index']);
    });


    /* =================================================================
        3. CUSTOMER MODULE (Yêu cầu Login)
        URL: /api/v1/customer/...
    ================================================================= */
    Route::prefix('customer')->middleware(['auth:sanctum', 'role:customer'])->group(function(){
        // ORDER ROUTES (Role: Customer)
        Route::prefix('orders')->group(function(){
            Route::post('/', [OrderController::class, 'store']);
            // Order History
            Route::get('/', [OrderHistoryController::class, 'index']);
            Route::get('/{id}', [OrderHistoryController::class, 'show']);
            Route::patch('/{id}/confirm',[OrderHistoryController::class, 'confirm']);
            Route::put('/{id}/cancel', [OrderHistoryController::class, 'cancel']);
        });
            
        //------------------------------------------------------------------------------------------------------------
        // NHÓM 1: USER ROUTES (Khách hàng dùng)
        Route::prefix('wallet')->group(function(){
            // GET /api/wallet/me -> Xem số dư & lịch sử
            Route::get('/', [WalletController::class, 'getMe']);
            // POST /api/wallet/deposit -> Nạp tiền (Auto-approve)
            Route::post('/deposit', [WalletController::class, 'deposit']);
        });

        Route::prefix('payment')->group(function(){
            Route::post( '/',[PaymentController::class, 'payByWallet']);
        });

        // ADDRESS ROUTES
        Route::prefix('addresses')->group(function(){
            Route::get('/', [AddressController::class, 'index']);
            Route::post('/', [AddressController::class, 'store']);
            Route::get('/{id}', [AddressController::class, 'show']);
            Route::put('/{id}', [AddressController::class, 'update']);
            Route::delete('/{id}', [AddressController::class, 'destroy']);
        
            // Route đặc biệt: Set default
            Route::patch('/{id}/default', [AddressController::class, 'setDefault']);
    });

        // CART ROUTES (Role: Customer)
        Route::prefix('cart')->group(function(){
            Route::get('/', [CartController::class, 'index']);      
            Route::post('/', [CartController::class, 'store']);     
            Route::put('/{id}', [CartController::class, 'update']); 
            Route::delete('/{id}', [CartController::class, 'destroy']); 
            Route::delete('/', [CartController::class, 'clear']);
        });

        // PRODUCT REVIEW ROUTES (Role: Customer)
        Route::post('/products/{id}/reviews', [ProductReviewController::class, 'store']);

        // Notification Routes
        Route::prefix('notifications')->group(function(){
            Route::get('/', [NotificationController::class, 'index']);
            Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::patch('/read-all', [NotificationController::class, 'markAllRead']);
        });

        // User detail show
        Route::get('/user/{id}',[AdminUserController::class, 'show']);

        //Profile
        Route::prefix('profile')->group(function(){
        Route::put('/avatar/{id}', [ProfileController::class, 'updateAvatar']);
        Route::put('/update-info/{id}', [ProfileController::class, 'updateInfo']);
        });
    });

    /* =================================================================
        4. ADMIN MODULE (Role: Admin)
        URL: /api/v1/admin/...
    ================================================================= */
    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function(){
        // 1. ADMIN WALLET ROUTES
            // POST /api/admin/wallet/refund -> Hoàn tiền cho khách
            Route::post('/wallet/refund', [AdminWalletController::class, 'refund']);
    
        // 2. ADMIN API (Dùng AdminProductController)
        Route::prefix('products')->group(function() {
            // GET /api/v1/admin/products
            Route::get('/', [AdminProductController::class, 'index']); 
        
            // GET /api/v1/admin/products/{id}
            Route::get('/{id}', [AdminProductController::class, 'show']);
            
            Route::post('/', [AdminProductController::class, 'store']);
            /* Lách luật bằng kỹ thuật Method Spoofing:
            Client (Frontend/Postman): Vẫn gửi Request là POST (để PHP đọc được file).
            Body Data: Gửi kèm một field ẩn tên là _method với giá trị là PUT.
            */ 
            Route::put('/{id}', [AdminProductController::class, 'update']);
            Route::delete('/{id}', [AdminProductController::class, 'destroy']);
        });

        // 3. Dashboard Analytics
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('admin.dashboard.api');
        Route::get('/dashboard/refresh', [DashboardController::class, 'refresh']);

        // 4. ADMIN CATEGORY ROUTES
        // Route resource tự động sinh: index, store, show, update, destroy
        Route::apiResource('categories', AdminCategoryController::class);

        // --- USER MANAGEMENT MODULE ---
    
        // 1. Analytics (Đặt trước route có tham số {id} để tránh xung đột)
        Route::get('/users/analytics', [AdminUserController::class, 'analytics']);

        // 2. List & Detail
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{id}', [AdminUserController::class, 'show']);

        // 3. Update Status (Dùng PATCH cho update 1 phần)
        Route::patch('/users/{id}/status', [AdminUserController::class, 'updateStatus']);

    });

    /* =================================================================
        5. SYSTEM UTILITIES
    ================================================================= */
    Route::middleware(['auth:sanctum'])->group(function () {
            // POST api/upload
            Route::post('/upload', [FileController::class, 'store']);
        });

    /* =================================================================
    6. ADMIN ORDER ROUTES (Role: Admin/Warehouse)
    ================================================================= */
    Route::middleware(['auth:sanctum', 'role:admin,warehouse'])->prefix('admin/orders')->group(function() {
        Route::patch('/{id}/status', [AdminOrderController::class, 'updateStatus']);
        Route::get('/',[AdminOrderController::class, 'index'] );
        Route::get('/{id}', [AdminOrderController::class, 'show']);
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