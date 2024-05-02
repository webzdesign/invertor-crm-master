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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->boolean('type')->comment('0 = In | 1 = Out');
            $table->dateTime('date');
            $table->integer('qty');
            $table->string('voucher')->nullable();
            $table->unsignedBigInteger('added_by');
            $table->tinyInteger('form')->comment('1 = Purchase | 2 = Sales');
            $table->tinyInteger('form_record_id');
            $table->foreign('added_by')->references('id')->on('users');
            $table->foreign('product_id')->references('id')->on('products');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
