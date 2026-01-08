<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            [
                'name' => 'New Year\'s Day',
                'start_date' => '2026-01-01',
                'end_date' => null,
            ],
            [
                'name' => 'Chinese New Year',
                'start_date' => '2026-02-17',
                'end_date' => '2026-02-18',
            ],
            [
                'name' => 'Hari Raya Aidilfitri',
                'start_date' => '2026-03-20',
                'end_date' => '2026-03-21',
            ],
            [
                'name' => 'Labour Day',
                'start_date' => '2026-05-01',
                'end_date' => null,
            ],
            [
                'name' => 'Hari Raya Haji',
                'start_date' => '2026-05-27',
                'end_date' => null,
            ],
            [
                'name' => 'Wesak Day',
                'start_date' => '2026-05-31',
                'end_date' => null,
            ],
            [
                'name' => 'Yang di-Pertuan Agong\'s Birthday',
                'start_date' => '2026-06-01',
                'end_date' => null,
            ],
            [
                'name' => 'Awal Muharram',
                'start_date' => '2026-06-17',
                'end_date' => null,
            ],
            [
                'name' => 'Maulidur Rasul',
                'start_date' => '2026-08-25',
                'end_date' => null,
            ],
            [
                'name' => 'National Day',
                'start_date' => '2026-08-31',
                'end_date' => null,
            ],
            [
                'name' => 'Malaysia Day',
                'start_date' => '2026-09-16',
                'end_date' => null,
            ],
            [
                'name' => 'Deepavali',
                'start_date' => '2026-11-08',
                'end_date' => null,
            ],
            [
                'name' => 'Christmas Day',
                'start_date' => '2026-12-25',
                'end_date' => null,
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create([
                'name'       => $holiday['name'],
                'start_date' => $holiday['start_date'],
                'end_date'   => $holiday['end_date'],
                'is_active'  => true,
            ]);
        }
    }
}
