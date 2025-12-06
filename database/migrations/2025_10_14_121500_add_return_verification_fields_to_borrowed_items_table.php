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
        Schema::table('borrowed_items', function (Blueprint $table) {
            $table->timestamp('return_pending_at')->nullable()->after('borrowed_at');
            $table->text('return_verification_notes')->nullable()->after('return_note');
            $table->unsignedBigInteger('return_verified_by')->nullable()->after('return_verification_notes');
            $table->timestamp('return_verified_at')->nullable()->after('return_verified_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowed_items', function (Blueprint $table) {
            $table->dropColumn('return_pending_at');
            $table->dropColumn('return_verification_notes');
            $table->dropColumn('return_verified_by');
            $table->dropColumn('return_verified_at');
        });
    }
};