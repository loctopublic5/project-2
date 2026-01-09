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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // 
            $table->string('code', 20)->unique(); // 
            $table->foreignId('user_id')->constrained('users'); // 
            
            // Note: ERD không bắt buộc discount_id (FK), nhưng giữ lại để tracking voucher là tốt.
            $table->unsignedBigInteger('discount_id')->nullable(); 
            
            // 1. Thêm payment_status (BẮT BUỘC theo ERD để biết khách trả tiền chưa) 
            $table->string('payment_status', 20)->default('unpaid');

            // 2. Payment method (ERD là ENUM, nhưng để String cho linh hoạt tích hợp VNPAY/MOMO là OK) 
            $table->string('payment_method', 50)->default('cod'); 

            // Tiền nong
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);

            // 5. Status đơn hàng (Thêm default 'pending') 
            $table->string('status', 20)->default('pending');

            $table->json('shipping_address'); // Snapshot địa chỉ 
            $table->string('note', 500)->nullable(); // 
            
            $table->timestamps(); // 

            // --- PERFORMANCE INDEXING (Quan trọng) ---
            
            // 1. Index cho trang "Lịch sử đơn hàng của tôi"
            // Giúp query: WHERE user_id = X ORDER BY created_at DESC cực nhanh
            $table->index(['user_id', 'created_at']); 

            // 2. Index cho Admin lọc đơn theo trạng thái (vd: Tìm đơn Chờ xử lý)
            $table->index('status');
            
            // 3. Index check đơn chưa thanh toán
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
