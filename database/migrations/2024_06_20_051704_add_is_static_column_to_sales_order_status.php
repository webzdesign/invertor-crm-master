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
        Schema::table('sales_order_statuses', function (Blueprint $table) {
            $table->boolean('is_static')->default(0)->comment('0 = Non Static | 1 = Static')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_order_statuses', function (Blueprint $table) {
            $table->dropColumn('is_static');
        });
    }
};
