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
    Schema::create('discounts', function (Blueprint $table) {
    $table->id(); // Tương ứng BIGINT PK AI [cite: 5, 51]
    $table->string('code', 50)->unique(); // 
    $table->enum('type', ['percent', 'fixed']); // 
    $table->decimal('value', 15, 2); // 
    $table->decimal('min_order_value', 15, 2)->default(0); // 
    $table->integer('max_usage')->nullable(); // 
    $table->integer('used_count')->default(0); // 

    $table->timestamp('start_date')->useCurrent(); // Mặc định là thời điểm tạo 
    $table->timestamp('end_date')->useCurrent();   // Sẽ được cập nhật khi tạo Voucher 
    
    $table->timestamps(); // created_at, updated_at [cite: 6, 7]
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
