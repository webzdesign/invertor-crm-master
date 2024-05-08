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
        Schema::table('products', function (Blueprint $table) {
            $table->string('unique_number')->nullable()->change();
            $table->string('sales_price')->nullable()->change();
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->double('expense')->nullable()->change();
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->text('customer_address_line_2')->nullable()->change();
            $table->unsignedBigInteger('customer_country')->nullable()->change();
            $table->unsignedBigInteger('customer_state')->nullable()->change();
            $table->unsignedBigInteger('customer_city')->nullable()->change();

            $table->unsignedBigInteger('status')->change();
            $table->foreign('status')->references('id')->on('sales_order_statuses');

            $table->string('country_dial_code')->nullable()->after('customer_phone');
            $table->string('country_iso_code')->nullable()->after('country_dial_code');
        });

        Schema::table('procurement_costs', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['country_dial_code', 'country_iso_code']);
        });

        Schema::table('procurement_costs', function (Blueprint $table) {
            $table->dropColumn('role_id');
        });
    }
};
