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
            $table->boolean('skipped')->default(false)->after('executed');
        });

        Schema::table('change_order_status_triggers', function (Blueprint $table) {
            $table->boolean('skipped')->default(false)->after('executed');
        });

        Schema::table('change_order_users', function (Blueprint $table) {
            $table->boolean('skipped')->default(false)->after('executed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('add_task_to_order_triggers', function (Blueprint $table) {
            $table->dropColumn('skipped');
        });

        Schema::table('change_order_status_triggers', function (Blueprint $table) {
            $table->dropColumn('skipped');
        });

        Schema::table('change_order_users', function (Blueprint $table) {
            $table->dropColumn('skipped');
        });
    }
};
