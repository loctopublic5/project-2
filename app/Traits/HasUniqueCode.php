<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUniqueCode{
    /**
     * Sinh mã duy nhất và kiểm tra trùng lặp trong Database
     *
     * @param string $modelClass Class Model cần check (VD: WalletTransaction::class)
     * @param string $column     Tên cột cần check (VD: 'code')
     * @param string $prefix     Tiền tố (VD: 'DEP')
     * @param int    $length     Độ dài chuỗi ngẫu nhiên (không tính prefix)
     * @return string            Mã hoàn chỉnh (VD: DEP-XH8L9Z)
     */
    protected function generateUniqueCode($modelClass,$column,$prefix,$length=8){
        do{
            // 1. Sinh chuỗi ngẫu nhiên, viết hoa
            $randomString = Str::upper(Str::random($length));

            // 2. Ghép prefix
            $code = $prefix . '-' .$randomString;

            // 3. Kiểm tra trong DB (Sử dụng Model động)
            // $modelClass::where(...) tương đương WalletTransaction::where(...)
        }while (static::where($column, $code)->exists());

        return $code;
        }
}
