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
        Schema::create('commission_withdrawal_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_id');
            $table->unsignedBigInteger('user_id');
            $table->json('orders')->nullable();
            $table->dateTime('from');
            $table->dateTime('to');
            $table->double('amount');
            $table->tinyInteger('status')->default(0)->comment('0 = Pending | 1 = Accepted | 2 = Rejeted');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('bank_id')->references('id')->on('bank_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_withdrawal_histories');
    }
};
