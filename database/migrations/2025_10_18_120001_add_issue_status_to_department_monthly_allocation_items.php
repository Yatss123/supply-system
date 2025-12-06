<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('department_monthly_allocation_items', function (Blueprint $table) {
            $table->string('issue_status')->default('none')->after('target_issue_qty');
            $table->unsignedInteger('staged_issue_qty')->nullable()->after('issue_status');
        });
    }

    public function down(): void
    {
        Schema::table('department_monthly_allocation_items', function (Blueprint $table) {
            $table->dropColumn(['issue_status', 'staged_issue_qty']);
        });
    }
};