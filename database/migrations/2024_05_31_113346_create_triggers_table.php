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
        Schema::create('triggers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('status_id')->comment('current');
            $table->unsignedBigInteger('next_status_id')->comment('next');
            $table->integer('sequence')->nullable();
            $table->tinyInteger('type')->comment('1 = Add Task | 2 = Change Order Status | 3 = Change User');

            $table->string('time')->default('+1 minutes');
            $table->tinyInteger('action_type')->default(1)->comment('1 = After moved to this status | 2 = after created to this stage | 3 = after moved or created to this stage');
            $table->tinyInteger('time_type')->default(1)->comment('1 = Immediately | 2 = 5 minutes | 3 = 10 minutes | 4 = One day | 5 = Set interval');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('task_description')->nullable();

            $table->unsignedBigInteger('added_by');
            $table->unsignedBigInteger('updated_by');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('sales_order_statuses');
            $table->foreign('next_status_id')->references('id')->on('sales_order_statuses');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('added_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('triggers');
    }
};
