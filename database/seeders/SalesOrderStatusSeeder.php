<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SalesOrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'color' => '#00b7db',
                'name' => 'INCOMING ORDER',
                'type' => 0,
                'sequence' => null
            ],
            [
                'color' => '#c78800',
                'name' => 'DISTRIBUTION',
                'type' => 0,
                'sequence' => null
            ],
            [
                'color' => '#a9ebfc',
                'name' => 'NEW',
                'sequence' => 1,
                'type' => 1
            ],
            [
                'color' => '#bf4ec3',
                'name' => 'NO ANSWERED 1',
                'sequence' => 2,
                'type' => 1
            ],
            [
                'color' => '#128f92',
                'name' => 'NO ANSWERED 2',
                'sequence' => 3,
                'type' => 1
            ],
            [
                'color' => '#8bd747',
                'name' => 'CONFIRMED ORDER',
                'sequence' => 4,
                'type' => 1
            ],
            [
                'color' => '#ed8c3a',
                'name' => 'CANCELLED',
                'sequence' => 5,
                'type' => 1
            ],
            [
                'color' => '#ed5e3a',
                'name' => 'SCAMMER',
                'sequence' => 6,
                'type' => 1
            ],
            [
                'color' => '#bced3a',
                'name' => 'AGREED TO BUY',
                'sequence' => 7,
                'type' => 1
            ],
            [
                'color' => '#ed3a3a',
                'name' => 'CLOSED LOSS',
                'sequence' => 8,
                'type' => 1
            ],
            [
                'color' => '#4CAF50',
                'name' => 'CLOSED WIN',
                'sequence' => 9,
                'type' => 1
            ]
        ];

        foreach ($statuses as $status) {
            \App\Models\SalesOrderStatus::firstOrCreate(['slug' => Str::slug($status['name'])], [
                'name' => $status['name'],
                'slug' => Str::slug($status['name']),
                'sequence' => $status['sequence'],
                'color' => $status['color'],
                'type' => $status['type']
            ]);
        }
    }
}
