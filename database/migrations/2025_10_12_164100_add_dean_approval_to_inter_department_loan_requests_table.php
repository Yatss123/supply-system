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
            $table->unsignedBigInteger('dean_approved_by')->nullable()->after('admin_approval_notes');
            $table->timestamp('dean_approved_at')->nullable()->after('dean_approved_by');
            $table->text('dean_approval_notes')->nullable()->after('dean_approved_at');
            
            $table->foreign('dean_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inter_department_loan_requests', function (Blueprint $table) {
            $table->dropForeign(['dean_approved_by']);
            $table->dropColumn(['dean_approved_by', 'dean_approved_at', 'dean_approval_notes']);
        });
    }
};
