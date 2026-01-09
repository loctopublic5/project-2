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
        Schema::create('permissions', function (Blueprint $table) {
    $table->id();
    $table->string('name')->comment('Tên hiển thị (Duyệt đơn hàng)');
    $table->string('resource', 50); // VD: products
    $table->string('action', 50);   // VD: create
    $table->timestamps();
    $table->unique(['resource', 'action']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
