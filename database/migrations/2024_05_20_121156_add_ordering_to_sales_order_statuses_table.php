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
            $table->integer('sequence')->nullable()->after('slug');
            $table->string('color', 12)->nullable()->after('sequence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_order_statuses', function (Blueprint $table) {
            $table->dropColumn(['sequence', 'color']);
        });
    }
};
