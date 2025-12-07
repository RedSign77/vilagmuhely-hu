<?php

namespace Webtechsolutions\ContentEngine\Providers;

use Illuminate\Support\ServiceProvider;
use Webtechsolutions\ContentEngine\Services\WorldResourceService;
use Webtechsolutions\ContentEngine\Services\WorldBuilderService;
use Webtechsolutions\ContentEngine\Services\AdjacencyService;
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
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Load API routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
    }
}
