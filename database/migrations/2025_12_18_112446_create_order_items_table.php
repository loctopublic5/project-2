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
            $table->id(); // [cite: 83]
            
            // Khóa ngoại trỏ tới orders (thêm cascade để xóa order thì xóa luôn item)
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            
            // Khóa ngoại trỏ tới products
            // Cho phép Null. Nếu Product gốc bị xóa vĩnh viễn, set cột này về NULL.
            // Dữ liệu dòng này VẪN CÒN (nhờ các cột snapshot bên dưới) để báo cáo tài chính không bị sai.
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->onDelete('set null');

            // --- CÁC CỘT SNAPSHOT BẮT BUỘC THEO ERD ---
            
            // Lưu tên sản phẩm tại thời điểm mua (đề phòng sau này đổi tên SP)
            $table->string('product_name'); 
            $table->string('sku')->nullable();

            // CỘT BẠN ĐANG THIẾU: Lưu giá bán tại thời điểm mua
            $table->decimal('price_at_purchase', 15, 2); // 

            $table->integer('quantity'); // 

            // Lưu size/color (JSON), cho phép null
            $table->json('variant_snapshot')->nullable(); // 
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
