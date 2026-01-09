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
        // Bảng trung gian nối Role và Permission
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            
            // Khóa ngoại trỏ về bảng roles
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            
            // Khóa ngoại trỏ về bảng permissions
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            
            // Tránh trùng lặp (1 Role không thể gán 2 lần 1 Permission)
            $table->unique(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_role');
    }
};
