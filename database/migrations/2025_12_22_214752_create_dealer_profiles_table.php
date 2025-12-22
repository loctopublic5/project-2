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
    Schema::create('dealer_profiles', function (Blueprint $table) {
        $table->id();
        
        // 1. LIÊN KẾT VỚI USER (Quan hệ 1-1)
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        
        // 2. LIÊN KẾT VỚI TIER (Đại lý này thuộc hạng nào?)
        // nullable vì mới tạo có thể chưa xếp hạng ngay
        $table->foreignId('price_tier_id')->nullable()->constrained('price_tiers')->nullOnDelete();

        // 3. THÔNG TIN DOANH NGHIỆP
        $table->string('brand_name')->nullable(); // Tên cửa hàng/đại lý
        $table->string('tax_id')->nullable();     // Mã số thuế
        $table->string('address')->nullable();
        $table->string('phone_business')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealer_profiles');
    }
};
