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
        Schema::table('issued_items', function (Blueprint $table) {
            $table->boolean('available_for_borrowing')->default(true)->after('issued_by');
            $table->integer('borrowed_quantity')->default(0)->after('available_for_borrowing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issued_items', function (Blueprint $table) {
            $table->dropColumn(['available_for_borrowing', 'borrowed_quantity']);
        });
    }
};
