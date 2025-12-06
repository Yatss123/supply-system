<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the enum to include the new status values
        DB::statement("ALTER TABLE inter_department_loan_requests MODIFY COLUMN status ENUM('pending', 'dean_approved', 'lending_dean_approved', 'lending_approved', 'borrowing_confirmed', 'admin_approved', 'declined', 'completed', 'returned') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the original enum values
        DB::statement("ALTER TABLE inter_department_loan_requests MODIFY COLUMN status ENUM('pending', 'lending_approved', 'borrowing_confirmed', 'admin_approved', 'declined', 'completed', 'returned') DEFAULT 'pending'");
    }
};
