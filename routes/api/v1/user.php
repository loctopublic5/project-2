<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DealerRequestController;

Route::prefix('user')->group(function(){

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function(){
        // POST /api/v1/user/dealer-request
        Route::post('/dealer-request', [DealerRequestController::class, 'store']);
    });
    
});
?>