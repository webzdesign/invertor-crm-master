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
        Schema::dropIfExists('call_change_call_history_users');

        Schema::create('call_change_call_history_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('trigger_id')->nullable();
            $table->unsignedBigInteger('call_id')->nullable();
            $table->integer('current_status_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->tinyInteger('type')->comment("1 = Immediately\n2 = 5min\n3 = 10min\n4 = 1day\n5 = custom")->default(1);
            $table->tinyInteger('main_type')->comment("1 = after added in this status\n2 = after moved to this status\n3 = after move or create to this status")->default(1);
            $table->boolean('executed')->default(false)->comment("0 = toBeExecuted\n1 = Executed");
            $table->boolean('skipped')->default(false);
            $table->string('time')->nullable();
            $table->string('remaining_time')->nullable();
            $table->dateTime('executed_at')->nullable();

            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('call_id')->references('id')->on('call_histories');
            $table->foreign('status_id')->references('id')->on('call_task_statuses');
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
        Schema::dropIfExists('call_change_call_history_users');
    }
};
