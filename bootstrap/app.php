<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
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

        // Register middleware aliases
        $middleware->alias([
            'forge.redirect' => \App\Http\Middleware\HandleForgeProfileRedirects::class,
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

        // Clean up user activity logs older than 90 days - runs daily at 4:00 AM
        $schedule->command('user-manager:cleanup-activity-logs --days=90')
            ->dailyAt('04:00')
            ->onOneServer();

        // Process crystal metric updates - runs every 30 minutes
        $schedule->command('crystal:process-updates')
            ->everyThirtyMinutes()
            ->onOneServer();

        // Clean up unused custom content categories and tags - runs daily at 5:00 AM
        $schedule->command('content:cleanup-unused-metadata')
            ->dailyAt('05:00')
            ->onOneServer();

        // Clean up expired invitations - runs daily at 1:00 AM
        $schedule->command('invitations:cleanup')
            ->dailyAt('01:00')
            ->onOneServer();

        $schedule->command('emails:process-scheduled')
            ->everyMinute()
            ->withoutOverlapping();

    })
    ->create();
