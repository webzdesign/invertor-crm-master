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
        Schema::create('manage_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('possible_status');
            $table->boolean('task')->default(false)->comment('0 = No Task | 1 = Create Task');
            $table->boolean('for_admin')->default(false);
            $table->unsignedBigInteger('responsible')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('responsible')->references('id')->on('roles');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('status_id')->references('id')->on('sales_order_statuses');
            $table->foreign('possible_status')->references('id')->on('sales_order_statuses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manage_statuses');
    }
};
