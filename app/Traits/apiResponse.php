<?php

namespace App\Traits;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

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
        $meta = [];
        $resultData = $data;

        // 1. Kiểm tra nếu data là Resource Collection (được bọc bởi ::collection($paginate))
        if ($data instanceof ResourceCollection) {
            // Lấy dữ liệu phân trang gốc bên trong
            if ($data->resource instanceof AbstractPaginator) {
                $paginator = $data->resource;
                
                // Tách meta
                $meta = [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total_items'  => $paginator->total(),
                    'total_pages'  => $paginator->lastPage(),
                ];

                // Data lúc này sẽ là mảng các item đã được qua hàm toArray() của Resource
                // resolve() giúp biến đổi Resource thành mảng thuần
                $resultData = $data->resolve(); 
            }
        } 
        // 2. Kiểm tra nếu data là Paginator thuần (không dùng Resource)
        else if ($data instanceof AbstractPaginator) {
            $meta = [
                'current_page' => $data->currentPage(),
                'per_page'     => $data->perPage(),
                'total_items'  => $data->total(),
                'total_pages'  => $data->lastPage(),
            ];
            $resultData = $data->items();
        }

        // Cấu trúc phản hồi chuẩn
        $response = [
            'status'  => true,
            'message' => $message,
            'data'    => $resultData,
        ];

        // Chỉ thêm meta nếu có phân trang
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
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