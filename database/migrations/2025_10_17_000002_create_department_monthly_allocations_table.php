<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('department_monthly_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            // Month in format YYYY-MM
            $table->string('month', 7);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['department_id', 'month']);
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_monthly_allocations');
    }
};