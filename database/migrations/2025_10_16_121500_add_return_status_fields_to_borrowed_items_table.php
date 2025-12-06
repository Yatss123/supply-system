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
        Schema::table('borrowed_items', function (Blueprint $table) {
            // Add status for returned items and optional missing/damaged details
            $table->enum('returned_status', ['returned', 'returned_with_missing', 'returned_with_damage'])
                ->nullable()
                ->after('returned_at');
            $table->integer('missing_count')->nullable()->default(0)->after('returned_status');
            $table->integer('damaged_count')->nullable()->default(0)->after('missing_count');
            $table->string('damage_severity')->nullable()->after('damaged_count');
            $table->text('damage_description')->nullable()->after('damage_severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowed_items', function (Blueprint $table) {
            $table->dropColumn(['returned_status', 'missing_count', 'damaged_count', 'damage_severity', 'damage_description']);
        });
    }
};