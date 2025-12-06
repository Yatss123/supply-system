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
            // Drop the existing user-based requester field
            if (Schema::hasColumn('restock_requests', 'requested_by')) {
                $table->dropConstrainedForeignId('requested_by');
            }
        });

        Schema::table('restock_requests', function (Blueprint $table) {
            // Add department-based requester field
            $table->foreignId('requested_department_id')->nullable()->after('supplier_id')
                ->constrained('departments')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restock_requests', function (Blueprint $table) {
            // Drop department-based requester field
            if (Schema::hasColumn('restock_requests', 'requested_department_id')) {
                $table->dropConstrainedForeignId('requested_department_id');
            }
        });

        Schema::table('restock_requests', function (Blueprint $table) {
            // Restore user-based requester field
            $table->foreignId('requested_by')->nullable()->after('supplier_id')
                ->constrained('users')->nullOnDelete();
        });
    }
};