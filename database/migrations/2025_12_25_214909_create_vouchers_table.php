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
    $table->string('code')->unique(); // Index tự động để query nhanh
    $table->string('type'); // ENUM: 'fixed' (trừ tiền thẳng) hoặc 'percent' (% giá trị)
    $table->decimal('value', 12, 2); // Giá trị giảm (VD: 50000 hoặc 10 (10%))
    
    // Điều kiện áp dụng
    $table->decimal('min_order_value', 12, 2)->default(0); // Đơn tối thiểu
    $table->decimal('max_discount_amount', 12, 2)->nullable(); // Giảm tối đa (Cho loại percent)
    $table->integer('limit_per_user')->default(1); // Mỗi người dùng tối đa bao nhiêu lần
    
    // Quản lý số lượng & Thời gian
    $table->integer('quantity')->default(0); // Số lượng còn lại
    $table->dateTime('start_date');
    $table->dateTime('end_date');
    
    $table->boolean('is_active')->default(true);
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
