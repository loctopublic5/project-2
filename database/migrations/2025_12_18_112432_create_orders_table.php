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
    $table->id();
    $table->string('code', 20)->unique();
    $table->foreignId('user_id')->constrained('users');
    $table->unsignedBigInteger('discount_id')->nullable();
    $table->enum('status', ['pending', 'confirmed', 'shipping', 'completed', 'cancelled']); 
    $table->decimal('subtotal', 15, 2);
    $table->decimal('tax', 15, 2)->default(0);
    $table->decimal('shipping_fee', 15, 2)->default(0);
    $table->decimal('discount_amount', 15, 2)->default(0);
    $table->decimal('total_amount', 15, 2);
    $table->string('payment_method', 50); // cod, vnpay... [cite: 54]
    $table->json('shipping_address'); // Snapshot địa chỉ [cite: 58, 60]
    $table->string('note', 500)->nullable();
    $table->timestamps();
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
