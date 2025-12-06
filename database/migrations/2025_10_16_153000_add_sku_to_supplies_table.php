<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('name');
        });

        // Backfill SKUs for existing supplies
        try {
            $supplies = \App\Models\Supply::with('categories')->get();
            foreach ($supplies as $supply) {
                $categoryName = optional($supply->categories->first())->name ?? '';
                $sku = \App\Models\Supply::generateSku($supply->name ?? '', $supply->unit ?? '', $categoryName);
                $supply->sku = $sku;
                $supply->save();
            }
        } catch (\Throwable $e) {
            // In case models are unavailable during migration in certain environments
            // leave SKUs nullable and allow application layer to fill later
        }
    }

    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn('sku');
        });
    }
};