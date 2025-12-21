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
        Schema::create('dealer_requests', function (Blueprint $table) {
        // 1. Liên kết với bảng users
        // constrained() tự hiểu là nối với id bảng users
        // cascadeOnDelete() để nếu xóa user thì xóa luôn yêu cầu (tránh rác DB)
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();

        // 2. Trạng thái (State Machine đơn giản)
        // Dùng ENUM để giới hạn giá trị hợp lệ ngay từ Database
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

        // 3. Ghi chú của Admin (Nullable vì lúc tạo chưa có note)
        $table->text('admin_note')->nullable();

        $table->timestamp('approved_at')->nullable();

        $table->timestamps(); // Lưu created_at (thời điểm gửi yêu cầu)
    });
}

    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealer_requests');
    }
};
