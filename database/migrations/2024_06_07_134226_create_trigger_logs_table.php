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
        Schema::create('trigger_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('trigger_id');
            $table->integer('order_id');
            $table->integer('user_id');
            $table->integer('watcher_id')->comment('User (if user changed) or robot (if changed by cron)');
            $table->integer('next_status_id')->nullable();
            $table->integer('current_status_id')->nullable();
            $table->text('description')->nullable();
            $table->integer('type')->nullable()->comment('1 = Task | 2 = Change Status | 3 = Change User');
            $table->integer('time_type')->nullable()->comment('1 = Immediatly | 2 = 5min | 3 = 10min | 4 = 1day | 5 = custom');
            $table->integer('hour')->nullable();
            $table->integer('minute')->nullable();
            $table->string('time')->nullable();
            $table->dateTime('executed_at')->nullable();
            $table->text('from_status')->nullable();
            $table->text('to_status')->nullable();
            $table->integer('from_responsible_user')->nullable();
            $table->integer('to_responsible_user')->nullable();
            $table->boolean('executed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trigger_logs');
    }
};
