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
        Schema::create('reviews', function (Blueprint $table) {
    $table->id(); // BIGINT PK AI [cite: 44]
    $table->foreignId('user_id')->constrained('users'); // Người đánh giá [cite: 44]
    $table->foreignId('product_id')->constrained('products'); // Sản phẩm [cite: 44]
    $table->tinyInteger('rating')->unsigned(); // Số sao (1 đến 5) [cite: 44]
    $table->text('comment')->nullable(); // Nội dung đánh giá [cite: 44]
    $table->string('pages_url', 500)->nullable();
    $table->timestamp('created_at')->useCurrent(); // [cite: 44]
    
    // Ràng buộc rating từ 1-5 theo tài liệu [cite: 44]
    // Lưu ý: MySQL 8.0.16+ hỗ trợ CHECK constraint
    // $table->check('rating >= 1 AND rating <= 5'); 
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
