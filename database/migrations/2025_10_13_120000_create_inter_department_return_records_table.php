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
        if (Schema::hasTable('inter_department_return_records')) {
            Schema::table('inter_department_return_records', function (Blueprint $table) {
                // Ensure foreign keys exist with short names
                $table->foreign('inter_department_borrowed_item_id', 'fk_return_borrowed_item')
                    ->references('id')->on('inter_department_borrowed_items')
                    ->onDelete('cascade');
                $table->foreign('initiated_by', 'fk_return_initiated_by')
                    ->references('id')->on('users')
                    ->onDelete('cascade');
                $table->foreign('verified_by', 'fk_return_verified_by')
                    ->references('id')->on('users')
                    ->onDelete('set null');
            });
            return;
        }

        Schema::create('inter_department_return_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inter_department_borrowed_item_id');
            $table->unsignedBigInteger('initiated_by');
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('inter_department_borrowed_item_id', 'fk_return_borrowed_item')
                ->references('id')->on('inter_department_borrowed_items')
                ->onDelete('cascade');
            $table->foreign('initiated_by', 'fk_return_initiated_by')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('verified_by', 'fk_return_verified_by')
                ->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inter_department_return_records');
    }
};