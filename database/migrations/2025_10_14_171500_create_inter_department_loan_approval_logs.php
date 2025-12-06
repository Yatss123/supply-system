<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('inter_department_loan_approval_logs')) {
            Schema::create('inter_department_loan_approval_logs', function (Blueprint $table) {
                $table->id();
                // Using original column name to match any pre-existing table
                $table->unsignedBigInteger('inter_department_loan_request_id');
                $table->unsignedBigInteger('approver_id');
                $table->string('approver_role')->nullable();
                $table->string('action');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inter_department_loan_approval_logs');
    }
};