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
        Schema::create('borrowed_item_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('borrowed_item_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // borrowed, return_pending, verified_return
            $table->integer('quantity')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->foreign('borrowed_item_id')->references('id')->on('borrowed_items')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowed_item_logs');
    }
};