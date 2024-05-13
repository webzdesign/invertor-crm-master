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
        Schema::create('distribution_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distribution_id');
            $table->integer('product_id');
            $table->integer('qty')->nullable();
            $table->integer('from_driver')->nullable();
            $table->integer('to_driver')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('distribution_id')->references('id')->on('distributions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_items');
    }
};
