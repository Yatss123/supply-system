<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('supply_requests', function (Blueprint $table) {
        $table->id();
        $table->string('item_name');
        $table->integer('quantity');
        $table->string('unit');
        $table->text('description')->nullable();
        $table->foreignId('department_id')->constrained()->onDelete('cascade');
        $table->string('status')->default('pending'); // Status field
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supply_requests');
    }
};
