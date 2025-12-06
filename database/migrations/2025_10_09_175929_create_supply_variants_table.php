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
        Schema::create('supply_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_id')->constrained()->onDelete('cascade');
            $table->string('variant_name');
            $table->json('attributes'); // Store size, gender, color, etc.
            $table->integer('quantity')->default(0);
            $table->string('sku')->unique()->nullable();
            $table->decimal('price', 10, 2)->nullable(); // Optional variant-specific pricing
            $table->timestamps();
            
            // Index for better performance
            $table->index(['supply_id', 'variant_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supply_variants');
    }
};
