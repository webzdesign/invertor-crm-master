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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->integer('confirm_status')->default(1)->after('status')->comment('0 = Pending | 1 = Confirm | 2 = Reject');
            $table->unsignedBigInteger('confirm_by')->after('confirm_status')->nullable();

            $table->foreign('confirm_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['confirm_status', 'confirm_by']);
        });
    }
};
