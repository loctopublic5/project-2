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
    Schema::create('files', function (Blueprint $table) {
        $table->id();
        
        // 1. Sửa default thành 'public' cho hợp môi trường Dev
        $table->string('disk', 20)->default('public'); 
        
        $table->string('path', 255);
        
        // 2. Tăng độ dài lên 255 để không bị lỗi với file Office
        $table->string('mime_type')->nullable(); 
        
        // Size không bao giờ âm -> dùng unsigned
        $table->unsignedBigInteger('size'); 
        
        // 3. Thay vì khai báo lẻ tẻ, dùng morphs để vừa tạo cột, vừa tạo INDEX tự động
        // Nó sẽ tạo ra: target_type (string) và target_id (bigint) + Index\
        $table->morphs('target'); 

        // 4. Tạo đủ bộ created_at và updated_at
        $table->timestamps(); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
