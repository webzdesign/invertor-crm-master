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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->dateTime('delivery_date');
            $table->string('order_no');
            $table->string('customer_name');
            $table->string('customer_address_line_1');
            $table->string('customer_address_line_2');
            $table->string('customer_city');
            $table->string('customer_state');
            $table->string('customer_country');
            $table->string('customer_phone');
            $table->string('customer_postal_code');
            $table->string('customer_facebook')->nullable();

            $table->string('user_id')->nullable();
            $table->string('seller_id')->nullable();
            $table->double('seller_commission')->nullable();

            $table->softDeletes();
            $table->unsignedBigInteger('added_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 = Pending | 1 = Assigned | 2 = Delivered');
            $table->timestamps();

            $table->foreign('added_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
