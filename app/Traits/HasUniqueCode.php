<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUniqueCode {
    /**
     * @param string $modelClass Tên Class Model (VD: WalletTransaction::class)
     * @param string $column     Tên cột cần check
     * @param string $prefix     Tiền tố
     * @param int    $length     Độ dài
     */
    protected function generateUniqueCode($modelClass, $column, $prefix, $length = 8) {
        do {
            $randomString = Str::upper(Str::random($length));
            $code = $prefix . '-' . $randomString;

            // Sử dụng $modelClass thay vì static
        } while ($modelClass::where($column, $code)->exists());

        return $code;
    }
}