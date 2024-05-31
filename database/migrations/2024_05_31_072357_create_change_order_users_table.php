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
        Schema::create('change_order_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('added_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('sales_orders');
            $table->foreign('status_id')->references('id')->on('sales_order_statuses');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('added_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_order_users');
    }
};
