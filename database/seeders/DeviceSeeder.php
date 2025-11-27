<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeviceSeeder extends Seeder
{
    public function run()
    {
        DB::table('devices')->insert([
            [
                'asset_id' => '9',
                'device_name' => 'TR001-1',
            ],
            [
                'asset_id' => '9',
                'device_name' => 'TR001-2',
            ],
            [
                'asset_id' => '9',
                'device_name' => 'TR001-3',
            ],
        ]);
    }
}
