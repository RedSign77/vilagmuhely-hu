<?php

namespace Webtechsolutions\Mailer\Providers;

use Illuminate\Support\ServiceProvider;
use Webtechsolutions\Mailer\Console\Commands\CleanupSentEmailsCommand;

class MailerServiceProvider extends ServiceProvider
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
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'mailer');

        // Publish views (optional, for customization)
        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/mailer'),
        ], 'mailer-views');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupSentEmailsCommand::class,
            ]);
        }
    }
}
