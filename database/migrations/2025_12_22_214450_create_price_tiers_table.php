<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void{
    Schema::create('price_tiers', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // VD: Silver, Gold, Platinum
        $table->string('slug')->unique(); // VD: silver, gold
        
        // Phần trăm giảm giá (VD: 10 nghĩa là 10%)
        $table->unsignedInteger('discount_percentage')->default(0); 
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_tiers');
    }
};
