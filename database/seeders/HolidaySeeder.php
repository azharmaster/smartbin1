<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;
use Carbon\Carbon;

class HolidaySeeder extends Seeder
{
    public function run()
    {
        $holidays = [
            // Single-day holidays
            [
                'name'         => 'New Year\'s Day',
                'holiday_date' => '2026-01-01',
                'start_date'   => null,
                'end_date'     => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'Chinese New Year',
                'holiday_date' => null,
                'start_date'   => '2026-02-17',
                'end_date'     => '2026-02-18',
                'is_active'    => true,
            ],
            [
                'name'         => 'Hari Raya Aidilfitri',
                'holiday_date' => null,
                'start_date'   => '2026-03-20',
                'end_date'     => '2026-03-21',
                'is_active'    => true,
            ],
            [
                'name'         => 'Labour Day',
                'holiday_date' => '2026-05-01',
                'start_date'   => null,
                'end_date'     => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'Hari Raya Haji',
                'holiday_date' => '2026-05-27',
                'start_date'   => null,
                'end_date'     => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'Wesak Day',
                'holiday_date' => '2026-05-31',
                'start_date'   => null,
                'end_date'     => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'Yang di-Pertuan Agong\'s Birthday',
                'holiday_date' => '2026-06-01',
                'start_date'   => null,
                'end_date'     => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'Awal Muharram (Islamic New Year)',
                'holiday_date' => '2026-06-17',
                'start_date'   => null,
                'end_date'     => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'The Prophet Muhammad\'s Birthday (Maulidur Rasul)',
                'holiday_date' => '2026-08-25',
                'start_date'   => null,
                'end_date'     => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'Malaysia\'s National Day',
                'holiday_date' => '2026-08-31',
                'start_date'   => null,
                'end_date'     => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'Malaysia Day',
                'holiday_date' => '2026-09-16',
                'start_date'   => null,
                'end_date'     => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'Deepavali',
                'holiday_date' => '2026-11-08',
                'start_date'   => null,
                'end_date'     => null,
                'is_active'    => true,
            ],
            [
                'name'         => 'Christmas Day',
                'holiday_date' => '2026-12-25',
                'start_date'   => null,
                'end_date'     => null,
                'is_active'    => true,
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::create($holiday);
        }
    }
}
