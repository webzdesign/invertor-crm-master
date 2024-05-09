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
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('form')->comment('1 = Sales Order')->default(1);
            $table->integer('form_record_id');
            $table->integer('item_id')->nullable();
            $table->double('bonus_actual_amount')->default(0);
            $table->integer('item_qty')->default(0);
            $table->double('bonus_amount')->default(0);
            $table->double('item_amount')->default(0);
            $table->unsignedBigInteger('added_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('added_by')->references('id')->on('users');
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->double('commission_actual_amount')->default(0);
            $table->integer('item_qty')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bonuses');

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn(['commission_actual_amount', 'item_qty']);
        });
    }
};
