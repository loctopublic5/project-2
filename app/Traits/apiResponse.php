<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

trait ApiResponse
{
    /**
     * Trả về Success Response
     * * @param mixed $data Dữ liệu trả về (Resource, Array, Object...)
     * @param string $message Thông báo (Mặc định: 'Success')
     * @param int $statusCode HTTP Code (Mặc định: 200 OK)
     */
    protected function success(mixed $data = null, string $message = 'Success', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    /**
     * Trả về Error Response
     * * @param string $message Thông báo lỗi
     * @param int $statusCode HTTP Code (Mặc định: 400 Bad Request)
     * @param mixed $errors Chi tiết lỗi (Validation errors, Exception trace...) - Optional
     */
    protected function error(string $message = 'Error', int $statusCode = Response::HTTP_BAD_REQUEST, mixed $errors = null): JsonResponse
    {
        $response = [
            'status'  => false,
            'message' => $message,
        ];

        // Chỉ thêm field 'errors' nếu có dữ liệu, giúp JSON gọn gàng
        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}