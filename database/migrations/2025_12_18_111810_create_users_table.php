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
    Schema::create('users', function (Blueprint $table) {
    $table->id(); // BIGINT UNSIGNED [cite: 13]
    $table->string('email')->unique();
    $table->string('password');
    $table->string('full_name', 100)->nullable()->index();
    $table->string('phone', 20)->nullable();
    $table->string('avatar_url', 500)->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_login_at')->nullable();
    $table->timestamps(); // created_at, updated_at [cite: 13]
    $table->softDeletes(); // deleted_at [cite: 14]
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
