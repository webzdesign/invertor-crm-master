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
        Schema::create('add_task_to_order_triggers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->integer('current_status_id');
            $table->unsignedBigInteger('status_id');
            $table->text('description')->nullable();
            $table->text('completed_description')->nullable();
            $table->tinyInteger('type')->comment('1 = Immediatly | 2 = 5min | 3 = 10min | 4 = 1day | 5 = custom')->default(1);
            $table->tinyInteger('main_type')->comment('1 = after added in this status | 2 = after moved to this status | 3 = after move or create to this status')->default(1);
            $table->unsignedBigInteger('added_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('executed')->default(false)->comment('0 = toBeExecuted | 1 = Executed');
            $table->boolean('completed')->default(false)->comment('0 = InComplete | 1 = Complete');
            $table->string('time')->nullable();
            $table->dateTime('executed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('sales_orders');
            $table->foreign('status_id')->references('id')->on('sales_order_statuses');
            $table->foreign('added_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_task_to_order_triggers');
    }
};
