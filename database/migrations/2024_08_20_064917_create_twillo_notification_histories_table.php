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
        Schema::create('twillo_notification_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('to_number')->nullable();
            $table->string('from_number')->nullable();
            $table->bigInteger('user_type')->nullable()->comment('1-driver,2-seller');
            $table->longText('message')->nullable();
            $table->bigInteger('status_id')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->json('api_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('twillo_notification_histories');
    }
};
