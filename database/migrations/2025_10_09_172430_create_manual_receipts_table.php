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
        Schema::create('manual_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->date('receipt_date');
            $table->string('reference_number')->nullable();
            $table->decimal('cost_per_unit', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'verified', 'needs_review'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_receipts');
    }
};
