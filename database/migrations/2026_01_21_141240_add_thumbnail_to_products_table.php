<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $blueprint) {
            // Thêm cột thumbnail sau cột sku, độ dài 500 để thoải mái lưu path/url
            $blueprint->string('thumbnail', 500)->nullable()->after('sku');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $blueprint) {
            $blueprint->dropColumn('thumbnail');
        });
    }
};
