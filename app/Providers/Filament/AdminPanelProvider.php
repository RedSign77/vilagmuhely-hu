<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\EditProfile;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Webtechsolutions\QueueManager\Filament\Resources\CompletedJobResource;
use Webtechsolutions\QueueManager\Filament\Resources\FailedJobResource;
use Webtechsolutions\QueueManager\Filament\Resources\PendingJobResource;
use Webtechsolutions\Sessions\Filament\Resources\SessionResource;
use Webtechsolutions\Mailer\Filament\Resources\EmailTemplateResource;
use Webtechsolutions\Mailer\Filament\Resources\SentEmailResource;
use Webtechsolutions\Mailer\Filament\Pages\ComposeEmail;
use Webtechsolutions\UserManager\Filament\Resources\UserResource;
use Webtechsolutions\UserManager\Filament\Resources\RoleResource;
use Webtechsolutions\UserManager\Filament\Resources\UserActivityLogResource;
use Webtechsolutions\ContentEngine\Filament\Resources\ContentResource;
use Webtechsolutions\ContentEngine\Filament\Resources\ContentCategoryResource;
use Webtechsolutions\ContentEngine\Filament\Resources\ContentTagResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label('Edit Profile')
                    ->url(fn (): string => EditProfile::getUrl())
                    ->icon('heroicon-o-user-circle'),
                'world' => MenuItem::make()
                    ->label('My World')
                    ->url(fn (): string => route('world.my-structures'))
                    ->icon('heroicon-o-globe-alt'),
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->resources([
                UserResource::class,
                RoleResource::class,
                UserActivityLogResource::class,
                ContentResource::class,
                ContentCategoryResource::class,
                ContentTagResource::class,
                SessionResource::class,
                PendingJobResource::class,
                FailedJobResource::class,
                CompletedJobResource::class,
                EmailTemplateResource::class,
                SentEmailResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Pages\Dashboard::class,
                ComposeEmail::class,
                EditProfile::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                'panels::body.end',
                fn (): string => view('filament.footer.custom-footer')->render()
            );
    }
}
