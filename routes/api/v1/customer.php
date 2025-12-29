<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\WalletController;


Route::prefix('customer')->group(function(){

    // NHÓM 1: USER ROUTES (Khách hàng dùng)
    Route::prefix('wallet')->group(function(){
        Route::middleware('auth:scantum')->group(function(){
            // GET /api/wallet/me -> Xem số dư & lịch sử
            ROUTE::get ('/' , [WalletController::class, 'getMe']);

            // POST /api/wallet/deposit -> Nạp tiền (Auto-approve)
            Route::post('/deposit', [WalletController::class, 'deposit']);
        });
    });

});
?>