<?php

namespace Webtechsolutions\ContentEngine\Providers;

use Illuminate\Support\ServiceProvider;

class ContentEngineServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Load views if needed
        // $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'content-engine');
    }
}
