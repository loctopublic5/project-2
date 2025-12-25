<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 4. Bảng Vouchers (Mã giảm giá) [cite: 72, 73]
    Schema::create('vouchers', function (Blueprint $table) {
    $table->id();
    $table->string('code', 50)->unique();
    $table->enum('type', ['fixed', 'percent']); // Trừ tiền mặt hoặc %
    $table->decimal('value', 15, 2); // Giá trị giảm
    $table->decimal('min_order_value', 15, 2)->default(0); // Đơn tối thiểu
    $table->integer('quantity')->default(0); // Số lượng mã
    $table->timestamp('start_date')->nullable();
    $table->timestamp('end_date')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
