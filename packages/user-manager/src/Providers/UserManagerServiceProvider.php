<?php

namespace Webtechsolutions\UserManager\Providers;

use Illuminate\Support\ServiceProvider;

class UserManagerServiceProvider extends ServiceProvider
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
    }
}
