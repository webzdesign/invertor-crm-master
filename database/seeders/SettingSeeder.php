<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Setting::firstOrCreate(
        [
            'id' => 1,
            'title' => 'E-Bike-CRM',
            'favicon' => null,
            'logo' => null,
            'bonus' => 3,
            'seller_commission' => 5,
            'geocode_key' => 'AIzaSyA54jt-xQC6UuWa8f8rJcTYpn4qyJyJ6eA'
        ]);
    }
}
