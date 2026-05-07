<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('smartbin:simulate')
                 ->everyMinute()       // or ->everyThirtyMinutes()
                 ->withoutOverlapping();

        // Send monthly summary email on the last day of each month at 5:00 PM
        $schedule->command('summary:send-monthly')
                 ->monthlyOnLastDayAt('17:00');

        // Send collection trip summary PDF to all admins on the last day of each month at 5:10 PM
        $schedule->command('collection-trips:send-summary-email')
                 ->monthlyOnLastDayAt('17:10');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php'); // for artisan closures only
    }
}
