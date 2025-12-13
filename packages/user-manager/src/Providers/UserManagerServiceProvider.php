<?php

namespace Webtechsolutions\UserManager\Providers;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webtechsolutions\UserManager\Console\Commands\CleanupActivityLogsCommand;
use Webtechsolutions\UserManager\Listeners\LogFailedLogin;
use Webtechsolutions\UserManager\Listeners\LogPasswordChange;
use Webtechsolutions\UserManager\Listeners\LogSuccessfulLogin;
use Webtechsolutions\UserManager\Listeners\LogSuccessfulLogout;
use Webtechsolutions\UserManager\Observers\UserObserver;

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
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Register event listeners
        Event::listen(Login::class, LogSuccessfulLogin::class);
        Event::listen(Logout::class, LogSuccessfulLogout::class);
        Event::listen(Failed::class, LogFailedLogin::class);
        Event::listen(PasswordReset::class, LogPasswordChange::class);

        // Register observers
        User::observe(UserObserver::class);

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupActivityLogsCommand::class,
            ]);
        }
    }
}
