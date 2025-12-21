<?php 
namespace App\Traits;
use Illuminate\Http\JsonResponse;

trait ApiResponse{
    protected function success($data, string $message = 'Success', int $code = 200): JsonResponse{
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error($data, string $message = 'Error', int $code =400): JsonResponse{
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
?>