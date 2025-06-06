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
        // Drop columns if they already exist
        if (Schema::hasColumn('settings', 'moldcell_url')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('moldcell_url');
            });
        }

        if (Schema::hasColumn('settings', 'moldcell_auth_pbx_key')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('moldcell_auth_pbx_key');
            });
        }

        if (Schema::hasColumn('settings', 'moldcell_auth_crm_key')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('moldcell_auth_crm_key');
            });
        }

        // Add columns
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
        // Drop columns only if they exist
        if (Schema::hasColumn('settings', 'moldcell_url')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('moldcell_url');
            });
        }

        if (Schema::hasColumn('settings', 'moldcell_auth_pbx_key')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('moldcell_auth_pbx_key');
            });
        }

        if (Schema::hasColumn('settings', 'moldcell_auth_crm_key')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('moldcell_auth_crm_key');
            });
        }
    }
};
