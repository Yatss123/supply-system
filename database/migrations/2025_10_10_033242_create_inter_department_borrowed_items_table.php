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
        Schema::create('inter_department_borrowed_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inter_department_loan_request_id')->constrained('inter_department_loan_requests', 'id', 'idbi_loan_request_fk')->onDelete('cascade');
            $table->foreignId('issued_item_id')->constrained('issued_items', 'id', 'idbi_issued_item_fk')->onDelete('cascade');
            $table->foreignId('lending_department_id')->constrained('departments', 'id', 'idbi_lending_dept_fk')->onDelete('cascade');
            $table->foreignId('borrowing_department_id')->constrained('departments', 'id', 'idbi_borrowing_dept_fk')->onDelete('cascade');
            $table->integer('quantity_borrowed');
            $table->date('borrowed_date');
            $table->date('expected_return_date');
            $table->date('actual_return_date')->nullable();
            $table->enum('status', ['active', 'returned', 'overdue', 'lost', 'damaged'])->default('active');
            $table->text('condition_notes')->nullable();
            $table->text('return_notes')->nullable();
            $table->foreignId('borrowed_by')->constrained('users', 'id', 'idbi_borrowed_by_fk')->onDelete('cascade');
            $table->foreignId('returned_to')->nullable()->constrained('users', 'id', 'idbi_returned_to_fk')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inter_department_borrowed_items');
    }
};
