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
        Schema::dropIfExists('call_triggers');

        Schema::create('call_triggers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('status_id')->comment('current')->nullable();
            $table->unsignedBigInteger('next_status_id')->comment('next')->nullable();
            $table->integer('sequence')->nullable();
            $table->tinyInteger('type')->comment("1 = Add Task\n2 = Change Order Status\n3 = Change User\n4 = twillo notification");

            $table->string('time')->default('+1 minutes');

            $table->tinyInteger('hour')->nullable();
            $table->tinyInteger('minute')->nullable();

            $table->tinyInteger('action_type')->default(1)->comment("1 = After moved to this status\n2 = after created to this stage\n3 = after moved or created to this stage");
            $table->tinyInteger('time_type')->default(1)->comment("1 = Immediately\n2 = 5 minutes\n3 = 10 minutes\n4 = One day\n5 = Set interval");

            $table->text('task_description')->nullable();
            $table->bigInteger('twillo_notification_id')->nullable();

            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('call_task_statuses')->nullOnDelete();
            $table->foreign('next_status_id')->references('id')->on('call_task_statuses')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('added_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_triggers');
    }
};
