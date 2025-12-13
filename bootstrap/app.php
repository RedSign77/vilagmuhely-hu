<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // Exclude API endpoints from CSRF verification
        // (Frontend sends CSRF token in X-CSRF-TOKEN header, but tests need this exception)
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
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

        // Process crystal metric updates - runs every 30 minutes
        $schedule->command('crystal:process-updates')
            ->everyThirtyMinutes()
            ->onOneServer();
    })
    ->create();
