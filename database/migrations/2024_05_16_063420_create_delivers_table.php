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
        Schema::create('delivers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Driver');
            $table->unsignedBigInteger('so_id')->nullable();
            $table->unsignedBigInteger('soi_id');
            $table->unsignedBigInteger('added_by');
            $table->string('driver_lat');
            $table->string('driver_long');
            $table->string('delivery_location_lat');
            $table->string('delivery_location_long');
            $table->string('range')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1 = Assigned | 2 = Dispatched | 3 = Deliverd | 4 = Denied');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('so_id')->references('id')->on('sales_orders');
            $table->foreign('soi_id')->references('id')->on('sales_order_items');
            $table->foreign('added_by')->references('id')->on('users');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivers');
    }
};
