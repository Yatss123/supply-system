<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Temporary privilege type (e.g., 'admin')
            $table->string('temp_privilege_type')->nullable()->after('role_id');
            // Expiration timestamp for temporary privilege; null means indefinite
            $table->timestamp('temp_privilege_expires_at')->nullable()->after('temp_privilege_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['temp_privilege_type', 'temp_privilege_expires_at']);
        });
    }
};