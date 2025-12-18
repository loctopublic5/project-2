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
    $table->string('disk', 20)->default('s3'); 
    $table->string('path', 255);
    $table->string('mime_type', 50);
    $table->bigInteger('size');
    $table->string('target_type', 50); // VD: products [cite: 77]
    $table->unsignedBigInteger('target_id');
    $table->timestamp('created_at')->useCurrent();
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
