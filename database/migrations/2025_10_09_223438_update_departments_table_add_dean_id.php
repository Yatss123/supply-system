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
        Schema::table('departments', function (Blueprint $table) {
            // Add dean_id column as foreign key to users table
            $table->unsignedBigInteger('dean_id')->nullable()->after('department_name');
            $table->foreign('dean_id')->references('id')->on('users')->onDelete('set null');
            
            // Drop the current_head column as it's being replaced by dean_id
            $table->dropColumn('current_head');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // Drop foreign key and dean_id column
            $table->dropForeign(['dean_id']);
            $table->dropColumn('dean_id');
            
            // Re-add current_head column
            $table->string('current_head')->after('department_name');
        });
    }
};
