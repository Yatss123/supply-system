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
        Schema::table('inter_department_return_records', function (Blueprint $table) {
            if (!Schema::hasColumn('inter_department_return_records', 'missing_count')) {
                $table->unsignedInteger('missing_count')->nullable()->after('photo_path');
            }
            if (!Schema::hasColumn('inter_department_return_records', 'damaged_count')) {
                $table->unsignedInteger('damaged_count')->nullable()->after('missing_count');
            }
            if (!Schema::hasColumn('inter_department_return_records', 'damage_severity')) {
                // Use string to represent severity: minor, moderate, severe, total_loss
                $table->string('damage_severity', 20)->nullable()->after('damaged_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inter_department_return_records', function (Blueprint $table) {
            if (Schema::hasColumn('inter_department_return_records', 'damage_severity')) {
                $table->dropColumn('damage_severity');
            }
            if (Schema::hasColumn('inter_department_return_records', 'damaged_count')) {
                $table->dropColumn('damaged_count');
            }
            if (Schema::hasColumn('inter_department_return_records', 'missing_count')) {
                $table->dropColumn('missing_count');
            }
        });
    }
};