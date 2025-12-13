<?php

namespace Webtechsolutions\QueueManager\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webtechsolutions\QueueManager\Console\Commands\CleanupCompletedJobsCommand;
use Webtechsolutions\QueueManager\Listeners\CaptureJobBeforeProcessing;
use Webtechsolutions\QueueManager\Listeners\MoveCompletedJobToHistory;

class QueueManagerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register commands
        $this->commands([
            CleanupCompletedJobsCommand::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Register event listeners
        Event::listen(JobProcessing::class, CaptureJobBeforeProcessing::class);
        Event::listen(JobProcessed::class, MoveCompletedJobToHistory::class);

        // Schedule cleanup task
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('queue:cleanup-completed --days=7')
                ->daily()
                ->at('02:00')
                ->description('Clean up completed jobs older than 7 days');
        });

        // Resources are registered in AdminPanelProvider
    }
}
