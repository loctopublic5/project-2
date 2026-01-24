<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SkipNgrokWarning
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Thêm header để ngrok không hiện trang cảnh báo
        $response->headers->set('ngrok-skip-browser-warning', 'true');

        return $response;
    }
}