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
        // 3. Bảng Wallet Transactions (Lịch sử giao dịch) [cite: 47, 48]
    Schema::create('wallet_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('wallet_id')->constrained('user_wallets')->onDelete('cascade');
    $table->enum('type', ['deposit', 'payment', 'refund']); // Nạp, Trả, Hoàn tiền
    $table->decimal('amount', 15, 2);
    $table->string('reference_id', 50)->index(); // Order ID hoặc Bank Trans ID
    $table->string('description')->nullable();
    $table->enum('status', ['success', 'failed', 'pending'])->default('success');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
