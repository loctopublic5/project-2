<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', 
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => App\Http\Middleware\CheckPermission::class,
        ]);
        $middleware->alias([
            'role' => App\Http\Middleware\CheckRoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 1. Chỉ trả về JSON nếu request là API
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });
        // 2. Tùy chỉnh lỗi 404 (Không tìm thấy Route hoặc Model)
        $exceptions->render(function(NotFoundHttpException $e, Request $request){
            if($request->is('api/*')){
                return response()->json([
                    'status' => false,
                    'message' =>'404 Not Found'
                ], 404);
            }
        });
        // 3. Tùy chỉnh lỗi sai Method (VD: Route POST mà lại gọi GET)
        $exceptions->render(function(MethodNotAllowedHttpException $e, Request $request){
            return response()->json([
                'status' => false,
                'message' => 'Method not found'
            ], 405);
        });
        // 4. Bắt TẤT CẢ các lỗi còn lại (Lỗi code 500, lỗi DB...)
        // Đây là cái lưới cuối cùng để đảm bảo App không bao giờ chết
        $exceptions->render(function(Throwable $e, Request $request){
            if($request->is('api/*')){

                // --- BẮT ĐẦU PHẦN LOG ---
                // Chỉ log khi không phải lỗi 404/validation (để tránh rác log)
                Log::error('[API ERROR] ' . $e->getMessage(), [
                    'url'     => $request->fullUrl(),   // Lỗi ở đường dẫn nào?
                    'input'   => $request->all(),       // User gửi lên cái gì?
                    'file'    => $e->getFile(),         // File nào bị lỗi?
                    'line'    => $e->getLine(),         // Dòng bao nhiêu?
                    // 'trace' => $e->getTraceAsString() // Bật cái này nếu muốn xem dấu vết sâu (nặng file log)
                ]);
                // --- KẾT THÚC PHẦN LOG ---

                return response()->json([
                    'status' => false,
                    'message' => 'Server Error',
                    'debug' => env('APP_DEBUG') ? $e->getMessage() : null,
                ], 500);
            }
        });
    })->create();
