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
        Schema::create('call_histories', function (Blueprint $table) {
            $table->id();
            $table->string("uid")->unique();
            $table->foreignId('from_user_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('to_user_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('status_id')->nullable()->references('id')->on('call_task_statuses')->nullOnDelete();

            $table->string("type")->comment("in\nout");
            $table->string("status")->comment("success\nmissed\nno answer");
            $table->string("client")->nullable();
            $table->string("diversion")->nullable();
            $table->string("telnum_name")->nullable();
            $table->string("destination")->comment("user - The employee accepted the call.\ngroup - The call was missed by the department.\ntelnum ‑ External telephone number.\nivr - Self-service menu.\nonduty - Person on duty during off-hours.\nofftime ‑ Message about non-working hours.\nhello - The call ended on the greeting.\nvm - Answering machine, the client left a message.\nam - Answering machine message.")->nullable();
            $table->string("user")->nullable();
            $table->string("user_name")->nullable();
            $table->string("group_name")->nullable();
            $table->dateTime("start");
            $table->string("start_timezone");
            $table->integer("wait")->comment("Waiting time on line in second")->nullable();
            $table->integer("duration")->comment("Call duration in second")->nullable();
            $table->string("record")->nullable();
            $table->integer("rating")->nullable();
            $table->text("note")->nullable();
            $table->tinyInteger("missedstatus")->comment("1 - The client called back\n2 - Called back\n3 - They didn't call back\n4 - Couldn't get through")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_histories');
    }
};
