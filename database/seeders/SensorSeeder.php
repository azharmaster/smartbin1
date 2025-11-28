<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SensorSeeder extends Seeder
{
    public function run()
    {
        DB::table('sensors')->insert([
            [
                'device_id' =>	'1',
                'battery' => '34',
                'capacity' => '90',
                'time' => '2025-11-20 07:23:15',
                'network' => '1',
            ],
            [
                'device_id' =>	'2',
                'battery' => '60',
                'capacity' => '70',
                'time' => '2025-11-20 07:20:15',
                'network' => '2',
            ],
            [
                'device_id' =>	'3',
                'battery' => '54',
                'capacity' => '85',
                'time' => '2025-11-20 07:22:15',
                'network' => '3',
            ],
        ]);
    }
}
