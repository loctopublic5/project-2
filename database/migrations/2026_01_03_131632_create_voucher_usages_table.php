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
    Schema::create('voucher_usages', function (Blueprint $table) {
        $table->id();
        
        // Liên kết User (Người dùng)
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        
        // Liên kết Voucher (Mã nào)
        $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
        
        // Liên kết Order (Dùng cho đơn nào - Quan trọng để Audit)
        // Lưu ý: Có thể nullable nếu bạn tạo usage trước khi tạo order (giữ slot), 
        // nhưng chuẩn nhất là lưu cùng lúc tạo order.
        $table->foreignId('order_id')->constrained()->cascadeOnDelete();
        
        // Thời điểm sử dụng (Thường là lúc đặt đơn thành công)
        $table->timestamp('used_at')->useCurrent();
        
        $table->timestamps();
        
        // INDEXING (Performance Tuning)
        // Giúp query đếm số lần dùng cực nhanh: 
        // Usage::where('user_id', 1)->where('voucher_id', 5)->count()
        $table->index(['user_id', 'voucher_id']); 
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_usages');
    }
};
