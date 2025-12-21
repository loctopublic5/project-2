<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user =$request->user();
        // Safety check: Dù auth:sanctum đã lo, nhưng check lại cho chắc chắn logic
        if(!$user){
            return response()->json ([
                'status' => false,
                'message' => 'Unauthorized'
            ],401);
        }
        // 2. Logic kiểm tra quyền
        // Chúng ta sẽ loop qua danh sách các roles được phép vào cửa
        foreach ($roles as $role){
            if($user->hasRole($role)){
                return $next($request);
            }
        }
        return response()->json([
            'status' => false,
            'message' => 'Bạn không có quyền thực hiện hành động này.'
        ],403);
    }
}
