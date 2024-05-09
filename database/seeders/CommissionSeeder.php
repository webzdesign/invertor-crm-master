<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([200, 205, 210] as $price) {
            \App\Models\CommissionPrice::updateOrCreate(['price' => $price], ['price' => $price]);
        }
    }
}
