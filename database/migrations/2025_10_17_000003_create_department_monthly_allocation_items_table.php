<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create table if it does not exist
        if (!Schema::hasTable('department_monthly_allocation_items')) {
            Schema::create('department_monthly_allocation_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('allocation_id');
                $table->unsignedBigInteger('supply_id');
                $table->integer('min_stock_level')->default(0);
                $table->integer('issued_qty')->default(0);
                $table->integer('suggest_qty')->default(0);
                $table->boolean('low_stock')->default(false);
                $table->json('attributes')->nullable();
                $table->timestamps();

                // Foreign keys
                $table->foreign('allocation_id')->references('id')->on('department_monthly_allocations')->onDelete('cascade');
                $table->foreign('supply_id')->references('id')->on('supplies')->onDelete('cascade');
            });
        }

        // Add unique index with shorter, explicit name to avoid MySQL length limits
        Schema::table('department_monthly_allocation_items', function (Blueprint $table) {
            $table->unique(['allocation_id', 'supply_id'], 'alloc_supply_unique');
        });
    }

    public function down(): void
    {
        // Drop the unique index if present, then drop the table
        try {
            Schema::table('department_monthly_allocation_items', function (Blueprint $table) {
                $table->dropUnique('alloc_supply_unique');
            });
        } catch (\Throwable $e) {
            // ignore if index doesn't exist
        }

        Schema::dropIfExists('department_monthly_allocation_items');
    }
};