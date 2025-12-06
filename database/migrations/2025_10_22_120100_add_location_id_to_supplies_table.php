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
        Schema::table('supplies', function (Blueprint $table) {
            if (!Schema::hasColumn('supplies', 'location_id')) {
                $table->foreignId('location_id')
                    ->nullable()
                    ->after('unit')
                    ->constrained('locations')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            if (Schema::hasColumn('supplies', 'location_id')) {
                $table->dropConstrainedForeignId('location_id');
            }
        });
    }
};