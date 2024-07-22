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
            $table->boolean('is_sheet_added')->default(0)->comment('0-notsend, 1-sent')->after('status');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('is_sheet_added')->default(0)->comment('0-notsend, 1-sent')->after('is_approved');
        });
        Schema::table('commission_withdrawal_histories', function (Blueprint $table) {
            $table->boolean('is_sheet_added')->default(0)->comment('0-notsend, 1-sent')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['is_sheet_added']);
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['is_sheet_added']);
        });
        Schema::table('commission_withdrawal_histories', function (Blueprint $table) {
            $table->dropColumn(['is_sheet_added']);
        });
    }
};
