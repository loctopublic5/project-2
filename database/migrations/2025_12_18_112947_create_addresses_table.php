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
    Schema::create('addresses', function (Blueprint $table) {
    $table->id(); // BIGINT PK AI [cite: 30]
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Tham chiếu users.id [cite: 30]
    $table->string('recipient_name', 100); // Tên người nhận [cite: 30]
    $table->string('phone', 20); // SĐT người nhận [cite: 30]
    $table->string('city', 100); // Tỉnh/Thành phố [cite: 30]
    $table->string('district', 100); // Quận/Huyện [cite: 30]
    $table->string('ward', 100); // Phường/Xã [cite: 30]
    $table->string('street', 255); // Số nhà, tên đường [cite: 30]
    $table->boolean('is_default')->default(false); // Địa chỉ mặc định [cite: 30]
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
