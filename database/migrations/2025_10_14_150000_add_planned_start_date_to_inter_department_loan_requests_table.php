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
        Schema::table('inter_department_loan_requests', function (Blueprint $table) {
            $table->date('planned_start_date')->nullable()->after('expected_return_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inter_department_loan_requests', function (Blueprint $table) {
            $table->dropColumn('planned_start_date');
        });
    }
};