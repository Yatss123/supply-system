<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedTinyInteger('stock_update_reminder_day')
                ->nullable()
                ->comment('ISO weekday 1-7; null=disabled');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('stock_update_reminder_day');
        });
    }
};