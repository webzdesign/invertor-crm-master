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
        Schema::table('change_order_users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('change_order_status_triggers', function (Blueprint $table) {
            $table->tinyInteger('main_type')->comment('1 = after added in this status | 2 = after moved to this status | 3 = after move or create to this status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('change_order_users', function (Blueprint $table) {
        });
    }
};
