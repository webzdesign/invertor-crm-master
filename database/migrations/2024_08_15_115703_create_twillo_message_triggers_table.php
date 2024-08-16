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
        Schema::create('twillo_message_notifications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('status_id')->nullable();
            $table->bigInteger('responsibale_user_type')->nullable()->comment("1-driver,2-seller");
            $table->longtext('message')->nullable();
            $table->bigInteger('added_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('twillo_message_notifications');
    }
};
