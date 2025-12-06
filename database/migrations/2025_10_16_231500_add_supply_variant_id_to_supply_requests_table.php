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
        if (!Schema::hasColumn('supply_requests', 'supply_variant_id')) {
            Schema::table('supply_requests', function (Blueprint $table) {
                $table->foreignId('supply_variant_id')
                    ->nullable()
                    ->constrained('supply_variants')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('supply_requests', 'supply_variant_id')) {
            Schema::table('supply_requests', function (Blueprint $table) {
                $table->dropForeign(['supply_variant_id']);
                $table->dropColumn('supply_variant_id');
            });
        }
    }
};