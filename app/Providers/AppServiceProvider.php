<?php

namespace App\Providers;

use App\Http\Responses\RegistrationResponse;
use App\Listeners\QueueCrystalUpdateListener;
use App\Services\CrystalCalculatorService;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Webtechsolutions\ContentEngine\Events\ContentDownloadedEvent;
use Webtechsolutions\ContentEngine\Events\ContentPublishedEvent;
use Webtechsolutions\ContentEngine\Events\ContentRatedEvent;
use Webtechsolutions\ContentEngine\Events\ContentViewedEvent;

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
        // Register Crystal update event listeners
        Event::listen(ContentPublishedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentPublished']);
        Event::listen(ContentViewedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentViewed']);
        Event::listen(ContentDownloadedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentDownloaded']);
        Event::listen(ContentRatedEvent::class, [QueueCrystalUpdateListener::class, 'handleContentRated']);
    }
}
