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
            if (!Schema::hasColumn('inter_department_return_records', 'quantity_returned')) {
                $table->unsignedInteger('quantity_returned')->nullable()->after('inter_department_borrowed_item_id');
            }
            if (!Schema::hasColumn('inter_department_return_records', 'is_damaged')) {
                $table->boolean('is_damaged')->default(false)->after('quantity_returned');
            }
            if (!Schema::hasColumn('inter_department_return_records', 'damage_description')) {
                $table->text('damage_description')->nullable()->after('damage_severity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inter_department_return_records', function (Blueprint $table) {
            if (Schema::hasColumn('inter_department_return_records', 'damage_description')) {
                $table->dropColumn('damage_description');
            }
            if (Schema::hasColumn('inter_department_return_records', 'is_damaged')) {
                $table->dropColumn('is_damaged');
            }
            if (Schema::hasColumn('inter_department_return_records', 'quantity_returned')) {
                $table->dropColumn('quantity_returned');
            }
        });
    }
};