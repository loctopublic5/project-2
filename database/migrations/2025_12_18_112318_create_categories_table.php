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
    Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('parent_id')->nullable();
    $table->string('name', 100);
    $table->string('slug', 100)->unique()->index();
    $table->tinyInteger('level')->default(1);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->foreign('parent_id')->references('id')->on('categories');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
