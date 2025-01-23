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
            $table->string('first_name')->nullable()->after('long');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('billing_email')->nullable()->after('country_iso_code');
            $table->text('billing_address')->nullable()->after('billing_email');
            $table->string('billing_postal_code')->nullable()->after('billing_address');
            $table->string('billing_city')->nullable()->after('billing_postal_code');
            $table->string('city')->nullable()->after('billing_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('billing_email');
            $table->dropColumn('billing_address');
            $table->dropColumn('billing_postal_code');
            $table->dropColumn('billing_city');
            $table->dropColumn('city');
        });
    }
};
