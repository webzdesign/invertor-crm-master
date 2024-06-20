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
            $table->tinyInteger('price_matched')->default(0)->comment('0 Not Matched | 1 = Matched')->after('seller_commission');
            $table->double('sold_amount')->nullable()->comment('Amount after order closed win')->after('price_matched');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['price_matched', 'sold_amount']);
        });
    }
};
