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
        Schema::table('settings', function (Blueprint $table) {
            $table->after('logo', function (Blueprint $table) {
                $table->string('twilioAccountSid')->nullable();
                $table->string('twilioAuthToken')->nullable();
                $table->string('twilioUrl')->nullable();
                $table->string('twilioFromNumber')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['twilioAccountSid','twilioAuthToken','twilioUrl','twilioFromNumber']);
        });
    }
};
