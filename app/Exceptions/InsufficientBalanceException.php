<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    // Cách 1: Để trống cũng được, Controller sẽ tự xử lý message.
    
    // Cách 2 (Senior): Định nghĩa sẵn HTTP Status Code trả về
    // Khi ném lỗi này, Laravel tự hiểu đây là lỗi 400 (Bad Request), không phải 500.
    public function render($request)
    {
        return response()->json([
            'error'   => true,
            'message' => $this->getMessage(), // Lấy message ta truyền vào
            'code'    => 'INSUFFICIENT_BALANCE' // Mã lỗi để FE check
        ], 400);
    }
}
