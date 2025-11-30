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
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Clean up completed jobs older than 30 days - runs daily at 2:00 AM
        $schedule->command('queue:cleanup-completed --days=30')
            ->dailyAt('02:00')
            ->onOneServer();

        // Clean up sent emails older than 7 days - runs daily at 3:00 AM
        $schedule->command('mailer:cleanup-sent --days=7')
            ->dailyAt('03:00')
            ->onOneServer();

        // Clean up user activity logs older than 90 days - runs daily at 4:00 AM
        $schedule->command('user-manager:cleanup-activity-logs --days=90')
            ->dailyAt('04:00')
            ->onOneServer();
    })
    ->create();
