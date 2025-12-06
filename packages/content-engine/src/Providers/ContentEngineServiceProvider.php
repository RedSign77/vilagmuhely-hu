<?php

namespace Webtechsolutions\ContentEngine\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webtechsolutions\ContentEngine\Console\Commands\ProcessCrystalUpdatesCommand;
use Webtechsolutions\ContentEngine\Events\AchievementUnlockedEvent;
use Webtechsolutions\ContentEngine\Events\ContentDownloadedEvent;
use Webtechsolutions\ContentEngine\Events\ContentPublishedEvent;
use Webtechsolutions\ContentEngine\Events\ContentRatedEvent;
use Webtechsolutions\ContentEngine\Events\ContentViewedEvent;
use Webtechsolutions\ContentEngine\Listeners\QueueCrystalUpdateListener;
use Webtechsolutions\ContentEngine\Services\CrystalCalculatorService;

class ContentEngineServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind CrystalCalculatorService as singleton
        $this->app->singleton(CrystalCalculatorService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Load API routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');

        // Register event listeners
        Event::listen(ContentPublishedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentPublished']);
        Event::listen(ContentViewedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentViewed']);
        Event::listen(ContentDownloadedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentDownloaded']);
        Event::listen(ContentRatedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentRated']);
        Event::listen(AchievementUnlockedEvent::class, [QueueCrystalUpdateListener::class, 'handleAchievementUnlocked']);

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessCrystalUpdatesCommand::class,
            ]);
        }
    }
}
