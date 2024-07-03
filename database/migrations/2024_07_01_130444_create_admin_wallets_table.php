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
        Schema::create('admin_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('so_id')->nullable();
            $table->double('amount')->default(0);
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->integer('from_account')->nullable();
            $table->tinyInteger('source')->default(0)->comment('0 = recevied from driver');
            $table->boolean('received')->default(1)->comment('0 = Not received | 1 = Recevied');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('so_id')->references('id')->on('sales_orders');
            $table->foreign('sender_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_wallets');
    }
};
