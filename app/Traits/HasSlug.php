<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Tạo Slug chuẩn SEO từ chuỗi và xử lý trùng lặp
     * VD: "Áo Thun" -> "ao-thun" (nếu trùng thì "ao-thun-1")
     */
    protected function generateSlug(string $sourceString, string $column = 'slug', string $separator = '-'): string
    {
        // 1. Tạo slug
        $slug = Str::slug($sourceString, $separator);
        $originalSlug = $slug;
        $count = 1;

        // 2. Check trùng lặp
        while (static::where($column, $slug)->exists()) {
            $slug = $originalSlug . $separator . $count;
            $count++;
        }

        return $slug;
    }
}