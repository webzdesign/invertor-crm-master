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
                'sequence' => null,
                'is_static' => 1
            ],
            [
                'color' => '#a9ebfc',
                'name' => 'NEW',
                'sequence' => null,
                'type' => 1,
                'is_static' => 1
            ],
            [
                'color' => '#bf4ec3',
                'name' => 'NO ANSWERED 1',
                'sequence' => 1,
                'type' => 1,
                'is_static' => 0
            ],
            [
                'color' => '#128f92',
                'name' => 'NO ANSWERED 2',
                'sequence' => 2,
                'type' => 1,
                'is_static' => 0
            ],
            [
                'color' => '#8bd747',
                'name' => 'CONFIRMED ORDER',
                'sequence' => 3,
                'type' => 1,
                'is_static' => 0
            ],
            [
                'color' => '#ed8c3a',
                'name' => 'CANCELLED',
                'sequence' => 4,
                'type' => 1,
                'is_static' => 0
            ],
            [
                'color' => '#ed5e3a',
                'name' => 'SCAMMER',
                'sequence' => null,
                'type' => 1,
                'is_static' => 1
            ],
            [
                'color' => '#bced3a',
                'name' => 'AGREED TO BUY',
                'sequence' => 6,
                'type' => 1,
                'is_static' => 0
            ],
            [
                'color' => '#ed3a3a',
                'name' => 'CLOSED LOSS',
                'sequence' => 7,
                'type' => 1,
                'is_static' => 0
            ],
            [
                'color' => '#4CAF50',
                'name' => 'CLOSED WIN',
                'sequence' => null,
                'type' => 1,
                'is_static' => 1
            ]
        ];

        foreach ($statuses as $status) {
            \App\Models\SalesOrderStatus::firstOrCreate(['slug' => Str::slug($status['name'])], [
                'name' => $status['name'],
                'slug' => Str::slug($status['name']),
                'sequence' => $status['sequence'],
                'color' => $status['color'],
                'type' => $status['type'],
                'is_static' => $status['is_static']
            ]);
        }
    }
}
