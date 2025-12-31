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

            // --- ⚠️ CÁC CỘT QUAN TRỌNG ĐÃ SỬA/THÊM ---
            
            // 1. Thêm payment_status (BẮT BUỘC theo ERD để biết khách trả tiền chưa) 
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');

            // 2. Payment method (ERD là ENUM, nhưng để String cho linh hoạt tích hợp VNPAY/MOMO là OK) 
            $table->string('payment_method', 50)->default('cod'); 

            $table->decimal('subtotal', 15, 2); // Tổng tiền hàng trước thuế/phí
            $table->decimal('tax', 15, 2)->default(0);
            
            // 3. Phí ship (Theo ERD) 
            $table->decimal('shipping_fee', 15, 2)->default(0); 
            
            // 4. Giảm giá (ERD gọi là voucher_discount) 
            $table->decimal('discount_amount', 15, 2)->default(0); 

            $table->decimal('total_amount', 15, 2); // 

            // 5. Status đơn hàng (Thêm default 'pending') 
            $table->enum('status', ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'])
                    ->default('pending'); 

            $table->json('shipping_address'); // Snapshot địa chỉ 
            $table->string('note', 500)->nullable(); // 
            
            $table->timestamps(); // 
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
