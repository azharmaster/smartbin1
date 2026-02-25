<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'guest' => \App\Http\Middleware\Guest::class,
            'auth' => \App\Http\Middleware\Auth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('smartbin:simulate')
                 ->everyMinute()
                 ->withoutOverlapping();

        // Send monthly summary email on the last day of each month at 5:00 PM
        // Using cron expression: 0 17 L * * (L = last day of month)
        $schedule->command('summary:send-monthly')
                 ->cron('0 17 L * *');
    })
    ->create();
