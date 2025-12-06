<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Expand loan request status enum to include 'borrowed' and 'return_pending'
        DB::statement("ALTER TABLE inter_department_loan_requests MODIFY COLUMN status ENUM('pending', 'dean_approved', 'lending_dean_approved', 'lending_approved', 'borrowing_confirmed', 'admin_approved', 'borrowed', 'return_pending', 'declined', 'completed', 'returned') DEFAULT 'pending'");

        // Expand borrowed item status enum to include 'return_pending'
        DB::statement("ALTER TABLE inter_department_borrowed_items MODIFY COLUMN status ENUM('active', 'return_pending', 'returned', 'overdue', 'lost', 'damaged') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert loan request status enum to previous set without 'borrowed' and 'return_pending'
        DB::statement("ALTER TABLE inter_department_loan_requests MODIFY COLUMN status ENUM('pending', 'dean_approved', 'lending_dean_approved', 'lending_approved', 'borrowing_confirmed', 'admin_approved', 'declined', 'completed', 'returned') DEFAULT 'pending'");

        // Revert borrowed item status enum to previous set without 'return_pending'
        DB::statement("ALTER TABLE inter_department_borrowed_items MODIFY COLUMN status ENUM('active', 'returned', 'overdue', 'lost', 'damaged') DEFAULT 'active'");
    }
};