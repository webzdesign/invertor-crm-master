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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('form_id')->nullable()->comment('1 = Sales Order')->default(1);
            $table->integer('form_record_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->json('attachments')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('driver_id')->nullable();
            $table->integer('seller_id')->nullable();
            $table->string('voucher')->nullable();
            $table->boolean('transaction_type')->default(0)->comment('0 = Credit | 1 = Debit');
            $table->double('amount')->default(0);
            $table->string('year')->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();

            $table->foreign('added_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
