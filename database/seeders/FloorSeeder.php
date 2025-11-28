<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FloorSeeder extends Seeder
{
    public function run()
    {
        DB::table('floor')->insert([
            [
                'floor_name' => 'Concourse',
                'picture' => 'trx concourse.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'floor_name' => 'Ground',
                'picture' => 'trx ground.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
