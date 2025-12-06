<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('department_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('department_carts')->onDelete('cascade');
            $table->foreignId('supply_request_id')->nullable()->constrained('supply_requests')->onDelete('set null');
            $table->foreignId('supply_id')->nullable()->constrained('supplies')->onDelete('set null');
            $table->string('item_name');
            $table->string('unit')->nullable();
            $table->integer('quantity');
            $table->enum('item_type', ['consumable', 'grantable'])->default('consumable');
            $table->json('attributes')->nullable();
            $table->enum('status', ['pending', 'edited', 'removed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_cart_items');
    }
};