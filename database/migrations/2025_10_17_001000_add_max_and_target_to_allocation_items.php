<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('department_monthly_allocation_items', function (Blueprint $table) {
            if (!Schema::hasColumn('department_monthly_allocation_items', 'max_limit')) {
                $table->unsignedInteger('max_limit')->default(0)->after('suggest_qty');
            }
            if (!Schema::hasColumn('department_monthly_allocation_items', 'target_issue_qty')) {
                $table->unsignedInteger('target_issue_qty')->nullable()->after('max_limit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('department_monthly_allocation_items', function (Blueprint $table) {
            if (Schema::hasColumn('department_monthly_allocation_items', 'target_issue_qty')) {
                $table->dropColumn('target_issue_qty');
            }
            if (Schema::hasColumn('department_monthly_allocation_items', 'max_limit')) {
                $table->dropColumn('max_limit');
            }
        });
    }
};