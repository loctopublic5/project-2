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
    Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained('categories');
    $table->string('name', 255);
    $table->string('slug', 255)->unique()->index();
    $table->string('sku', 50)->unique()->index();
    $table->decimal('price', 15, 2);
    $table->decimal('sale_price', 15, 2)->nullable();
    $table->integer('stock_qty')->default(0);
    $table->text('description')->nullable();
    $table->integer('view_count')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
