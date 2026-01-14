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
        Schema::create('cart_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    
    $table->integer('quantity')->default(1);
    
    // Lưu các lựa chọn (Size, Color...) dưới dạng JSON
    // VD: {"size": "M", "color": "Red"}
    $table->json('options')->nullable(); 
    
    // Checkbox chọn mua (Mặc định là chọn)
    $table->boolean('selected')->default(true);
    
    $table->timestamps();

    // CONSTRAINT QUAN TRỌNG:
    // Một sản phẩm (cùng options) chỉ xuất hiện 1 lần trong 1 giỏ.
    // Nếu thêm lần nữa -> Update quantity chứ không tạo dòng mới.
    // (Tạm thời Unique theo cặp cart_id + product_id nếu chưa làm options phức tạp)
    $table->unique(['cart_id', 'product_id']); 
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
