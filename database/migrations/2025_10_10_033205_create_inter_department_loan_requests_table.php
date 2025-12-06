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
        Schema::create('inter_department_loan_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issued_item_id')->constrained('issued_items')->onDelete('cascade');
            $table->foreignId('lending_department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('borrowing_department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->integer('quantity_requested');
            $table->text('purpose');
            $table->date('expected_return_date');
            $table->enum('status', ['pending', 'lending_approved', 'borrowing_confirmed', 'admin_approved', 'declined', 'completed', 'returned'])->default('pending');
            $table->foreignId('lending_approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('lending_approved_at')->nullable();
            $table->text('lending_approval_notes')->nullable();
            $table->foreignId('borrowing_confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('borrowing_confirmed_at')->nullable();
            $table->text('borrowing_confirmation_notes')->nullable();
            $table->foreignId('admin_approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('admin_approved_at')->nullable();
            $table->text('admin_approval_notes')->nullable();
            $table->foreignId('declined_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('declined_at')->nullable();
            $table->text('decline_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inter_department_loan_requests');
    }
};
