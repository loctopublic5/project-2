<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUniqueCode{
    /**
     * Sinh mã duy nhất và kiểm tra trùng lặp
     * *@param string $column  Tên cột cần check (VD: 'sku')
     * @param string $prefix  Tiền tố (VD: 'SP')
     * @param int    $length  Độ dài chuỗi ngẫu nhiên (không tính prefix)
     */
    protected function generateUniqueCode($column,$prefix,$length=8){
        do{
            // 1. Sinh chuỗi ngẫu nhiên, viết hoa
            $randomString = Str::upper(Str::random($length));

            // 2. Ghép prefix
            $code = $prefix . '-' .$randomString;

            // 3. Kiểm tra trong DB (Sử dụng Model động)
            // $modelClass::where(...) tương đương WalletTransaction::where(...)
        } while (static::where($column, $code)->exists());

        return $code;
        }
}
