<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DealerRequestController;

Route::prefix('customer')->group(function(){

    Route::middleware(['auth:sanctum'])->group(function(){
        // POST /api/v1/customer/dealer-request
        Route::post('/dealer-request', [DealerRequestController::class, 'store']);
    });
    
});
?>