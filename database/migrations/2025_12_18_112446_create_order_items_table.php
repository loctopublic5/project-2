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
        Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
    $table->foreignId('product_id')->constrained('products');
    $table->string('product_name', 255); // Snapshot tên sản phẩm [cite: 67, 82]
    $table->integer('quantity');
    $table->decimal('price', 15, 2); // Giá tại thời điểm mua [cite: 67]
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
