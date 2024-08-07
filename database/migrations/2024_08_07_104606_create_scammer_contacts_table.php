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
        Schema::create('scammer_contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('so_id')->nullable();
            $table->string('dial_code')->nullable();
            $table->string('phone_number')->nullable();
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
        Schema::dropIfExists('scammer_contacts');
    }
};
