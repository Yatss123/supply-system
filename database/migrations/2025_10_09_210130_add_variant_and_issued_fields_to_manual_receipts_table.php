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
        Schema::table('manual_receipts', function (Blueprint $table) {
            $table->foreignId('supply_variant_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('issued_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('issued_at')->nullable();
            $table->enum('status', ['pending', 'verified', 'needs_review', 'issued'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manual_receipts', function (Blueprint $table) {
            $table->dropForeign(['supply_variant_id']);
            $table->dropForeign(['issued_to']);
            $table->dropColumn(['supply_variant_id', 'issued_to', 'issued_at']);
            $table->enum('status', ['pending', 'verified', 'needs_review'])->default('pending')->change();
        });
    }
};
