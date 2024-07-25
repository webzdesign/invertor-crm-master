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
        Schema::table('trigger_logs', function (Blueprint $table) {
            $table->after('current_status_id', function ($table) {
                $table->longText('allocated_driver_id')->nullable();
                $table->integer('assgined_driver_id')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trigger_logs', function (Blueprint $table) {
            $table->dropColumn(['allocated_driver_id','assgined_driver_id']);
        });
    }
};
