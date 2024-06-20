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
        Schema::create('sales_order_proof_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('so_id');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('so_id')->references('id')->on('sales_orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_proof_images');
    }
};
