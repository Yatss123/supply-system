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
        if (!Schema::hasTable('inter_department_loan_request_items')) {
            Schema::create('inter_department_loan_request_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('inter_department_loan_request_id');
                $table->unsignedBigInteger('issued_item_id');
                $table->unsignedInteger('quantity_requested');
                $table->text('notes')->nullable();
                $table->timestamps();

                // Use shorter, explicit foreign key names to avoid MySQL length limits
                $table->foreign('inter_department_loan_request_id', 'fk_idlri_request')
                    ->references('id')->on('inter_department_loan_requests')
                    ->onDelete('cascade');
                $table->foreign('issued_item_id', 'fk_idlri_issued_item')
                    ->references('id')->on('issued_items')
                    ->onDelete('restrict');
            });
        } else {
            Schema::table('inter_department_loan_request_items', function (Blueprint $table) {
                // Ensure columns exist
                if (!Schema::hasColumn('inter_department_loan_request_items', 'inter_department_loan_request_id')) {
                    $table->unsignedBigInteger('inter_department_loan_request_id');
                }
                if (!Schema::hasColumn('inter_department_loan_request_items', 'issued_item_id')) {
                    $table->unsignedBigInteger('issued_item_id');
                }

                // Add foreign keys with short names if they don't already exist
                $table->foreign('inter_department_loan_request_id', 'fk_idlri_request')
                    ->references('id')->on('inter_department_loan_requests')
                    ->onDelete('cascade');
                $table->foreign('issued_item_id', 'fk_idlri_issued_item')
                    ->references('id')->on('issued_items')
                    ->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inter_department_loan_request_items');
    }
};