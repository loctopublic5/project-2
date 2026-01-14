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
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    
    // Liên kết review với đơn hàng cụ thể
    $table->foreignId('order_id')->constrained()->onDelete('cascade'); 
    
    $table->unsignedTinyInteger('rating'); // 1 đến 5 (Dùng tinyInt cho nhẹ)
    $table->text('comment')->nullable();
    $table->boolean('is_active')->default(true); // Để Admin ẩn review nếu vi phạm
    $table->timestamps();

    // 🔥 UNIQUE INDEX: "Thần chú" chống Spam
    // Một User, với một Product, trong một Order => Chỉ được tồn tại 1 dòng review.
    $table->unique(['user_id', 'product_id', 'order_id'], 'unique_review_per_order');
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
