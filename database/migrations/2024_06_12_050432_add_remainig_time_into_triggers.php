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
        Schema::table('add_task_to_order_triggers', function (Blueprint $table) {
            $table->string('remaining_time')->nullable()->after('time');
        });

        Schema::table('change_order_status_triggers', function (Blueprint $table) {
            $table->string('remaining_time')->nullable()->after('time');
        });

        Schema::table('change_order_users', function (Blueprint $table) {
            $table->string('remaining_time')->nullable()->after('time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('add_task_to_order_triggers', function (Blueprint $table) {
            $table->dropColumn('remaining_time');
        });

        Schema::table('change_order_status_triggers', function (Blueprint $table) {
            $table->dropColumn('remaining_time');
        });

        Schema::table('change_order_users', function (Blueprint $table) {
            $table->dropColumn('remaining_time');
        });
    }
};
