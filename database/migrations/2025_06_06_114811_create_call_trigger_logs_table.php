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
        Schema::create('call_trigger_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('cron_id')->nullable();
            $table->integer('trigger_id');
            $table->integer('call_id');
            $table->integer('user_id')->nullable();
            $table->integer('watcher_id')->comment('User (if user changed) or robot (if changed by cron)')->nullable();
            $table->integer('next_status_id')->nullable();
            $table->integer('current_status_id')->nullable();
            $table->longText('allocated_driver_id')->nullable();
            $table->integer('assgined_driver_id')->nullable();
            $table->text('description')->nullable();
            $table->integer('type')->nullable()->comment("1 = Task\n2 = Change Status\n3 = Change User");
            $table->integer('time_type')->nullable()->comment("1 = Immediately\n2 = 5min\n3 = 10min\n4 = 1day\n5 = custom");
            $table->integer('main_type')->nullable()->comment("1 = after moved to this status\n2 = after added in this status\n3 = after move or create to this status");
            $table->integer('hour')->nullable();
            $table->integer('minute')->nullable();
            $table->string('time')->nullable();
            $table->dateTime('executed_at')->nullable();
            $table->json('from_status')->nullable();
            $table->json('to_status')->nullable();
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
        Schema::dropIfExists('call_trigger_logs');
    }
};
