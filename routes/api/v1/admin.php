<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DealerRequestController;

Route::prefix('admin')->group(function(){

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function(){
        // PUT /api/v1/admin/dealer-requests/{id_request}
        Route::put('/dealer-requests/{dealer_request}', [DealerRequestController::class, 'updateStatus']);
    });
    
});
?>