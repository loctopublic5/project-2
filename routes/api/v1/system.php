<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\System\FileController;


Route::prefix('admin')->group(function(){

    Route::middleware(['auth:sanctum'])->group(function () {
    // API Upload file dùng chung
    Route::post('/upload', [FileController::class, 'store']);
    });
    
});
?>