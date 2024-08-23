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
        Schema::table('twillo_message_notifications', function (Blueprint $table) {
           $table->string('template_id')->nullable()->after('responsibale_user_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('twillo_message_notifications', function (Blueprint $table) {
            $table->dropColumn(['template_id']);
        });
    }
};
