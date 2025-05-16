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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('moldcell_url')->nullable()->after('google_sheet_id');
            $table->string('moldcell_auth_pbx_key')->nullable()->after('moldcell_url');
            $table->string('moldcell_auth_crm_key')->nullable()->after('moldcell_auth_pbx_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('moldcell_url');
            $table->dropColumn('moldcell_auth_pbx_key');
            $table->dropColumn('moldcell_auth_crm_key');
        });
    }
};
