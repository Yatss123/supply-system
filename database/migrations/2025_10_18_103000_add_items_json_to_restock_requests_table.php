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
            // Store selected items for consolidated requests as JSON text
            $table->longText('items_json')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restock_requests', function (Blueprint $table) {
            $table->dropColumn('items_json');
        });
    }
};