<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminWalletController;

Route::prefix('admin')->group(function(){
    // NHÓM 2: ADMIN ROUTES (Quản trị viên dùng)
        Route::middleware(['auth:sanctum', 'role:admin'])->group(function(){
            // POST /api/admin/wallet/refund -> Hoàn tiền cho khách
            Route::post('/refund', [AdminWalletController::class, 'refund']);
        });
    

    
});
?>