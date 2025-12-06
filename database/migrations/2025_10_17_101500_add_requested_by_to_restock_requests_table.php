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
        Schema::table('restock_requests', function (Blueprint $table) {
            // Track who requested the restock (nullable for legacy records)
            $table->foreignId('requested_by')->nullable()->after('supplier_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restock_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requested_by');
        });
    }
};