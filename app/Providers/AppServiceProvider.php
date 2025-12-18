<?php

namespace App\Providers;

use App\Http\Responses\RegistrationResponse;
use App\Listeners\HandleInvitationAcceptance;
use App\Listeners\QueueCrystalUpdateListener;
use App\Models\Invitation;
use App\Models\User;
use App\Observers\UserObserver;
use App\Policies\ContentPolicy;
use App\Policies\InvitationPolicy;
use App\Services\CrystalCalculatorService;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Webtechsolutions\ContentEngine\Events\ContentDownloadedEvent;
use Webtechsolutions\ContentEngine\Events\ContentPublishedEvent;
use Webtechsolutions\ContentEngine\Events\ContentRatedEvent;
use Webtechsolutions\ContentEngine\Events\ContentReviewedEvent;
use Webtechsolutions\ContentEngine\Events\ContentViewedEvent;
use Webtechsolutions\ContentEngine\Models\Content;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register custom registration response
        $this->app->singleton(
            RegistrationResponseContract::class,
            RegistrationResponse::class
        );

        // Register Crystal Calculator Service
        $this->app->singleton(CrystalCalculatorService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Content::class, ContentPolicy::class);
        Gate::policy(Invitation::class, InvitationPolicy::class);

        // Register observers
        User::observe(UserObserver::class);

        // Register Crystal update event listeners
        Event::listen(ContentPublishedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentPublished']);
        Event::listen(ContentViewedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentViewed']);
        Event::listen(ContentDownloadedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentDownloaded']);
        Event::listen(ContentRatedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentRated']);
        Event::listen(ContentReviewedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentReviewed']);

        // Register invitation acceptance listener
        Event::listen(Registered::class, HandleInvitationAcceptance::class);
    }
}
