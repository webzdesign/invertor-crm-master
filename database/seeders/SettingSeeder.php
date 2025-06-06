<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $setting =  \App\Models\Setting::firstOrCreate(
            ['id' =>1],[
            'id' => 1,
            'title' => 'E-Bike-CRM',
            'favicon' => null,
            'logo' => null,
            'bonus' => 3,
            'seller_commission' => 5,
            'geocode_key' => 'AIzaSyA54jt-xQC6UuWa8f8rJcTYpn4qyJyJ6eA',
            'google_sheet_id' => ''
        ]);

        if ( empty($setting->moldcell_url) && empty($setting->moldcell_auth_pbx_key) && empty($setting->moldcell_auth_crm_key) ) {
            $setting->moldcell_url = "https://transpollogistic.pbx.moldcell.md";
            $setting->moldcell_auth_pbx_key = "b95f2d49-1c5c-48bd-aaa1-4239bcd7ad48";
            $setting->moldcell_auth_crm_key = "62656e74";
            $setting->save();
        }
    }
}
