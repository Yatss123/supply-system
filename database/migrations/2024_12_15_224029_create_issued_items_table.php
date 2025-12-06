<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIssuedItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issued_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplies_id');
            $table->unsignedBigInteger('departments_id'); // Department that receives the supply
            $table->integer('quantity');
            $table->date('issued_on');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('supplies_id')->references('id')->on('supplies')->onDelete('cascade');
            $table->foreign('departments_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('issued_items');
    }
}
