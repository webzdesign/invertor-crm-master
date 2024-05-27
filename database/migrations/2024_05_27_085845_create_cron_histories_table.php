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
        Schema::create('cron_histories', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('status')->default(1)->comment('1 = processing | 2 = success | 3 = failed');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->unsignedBigInteger('trigger_id')->nullable();
            $table->tinyInteger('trigger_type')->nullable()->default(1)->comment('1 = Change Lead Stage');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_histories');
    }
};
