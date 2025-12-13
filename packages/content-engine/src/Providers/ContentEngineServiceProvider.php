<?php

namespace Webtechsolutions\ContentEngine\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webtechsolutions\ContentEngine\Events\ContentPublishedEvent;
use Webtechsolutions\ContentEngine\Listeners\AwardResourcesOnContentPublished;
use Webtechsolutions\ContentEngine\Models\Content;
use Webtechsolutions\ContentEngine\Observers\ContentObserver;
use Webtechsolutions\ContentEngine\Services\AdjacencyService;
use Webtechsolutions\ContentEngine\Services\WorldBuilderService;
use Webtechsolutions\ContentEngine\Services\WorldResourceService;
use Webtechsolutions\ContentEngine\Services\ZoneService;

class ContentEngineServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind World services as singletons
        $this->app->singleton(ZoneService::class);
        $this->app->singleton(AdjacencyService::class);
        $this->app->singleton(WorldResourceService::class);
        $this->app->singleton(WorldBuilderService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Load API routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');

        // Register Content observer
        Content::observe(ContentObserver::class);

        // Register event listeners
        Event::listen(ContentPublishedEvent::class, AwardResourcesOnContentPublished::class);
    }
}
