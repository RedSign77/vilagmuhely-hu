<?php

namespace Webtechsolutions\Sessions\Providers;

use Illuminate\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Resources are auto-discovered by Filament when properly namespaced
        // No need to manually register them in Filament 3
    }
}
