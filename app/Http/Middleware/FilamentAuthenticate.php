<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Database\Eloquent\Model;

class FilamentAuthenticate extends Middleware
{
    /**
     * @param  array<string>  $guards
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);

            return; /** @phpstan-ignore-line */
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var Model $user */
        $user = $guard->user();

        $panel = Filament::getCurrentPanel();

        // Check if user can access panel
        if ($user instanceof FilamentUser && ! $user->canAccessPanel($panel)) {
            // Force logout the user
            $guard->logout();

            // Invalidate the session
            $request->session()->invalidate();

            // Regenerate CSRF token
            $request->session()->regenerateToken();

            // Flash notification message
            $request->session()->flash('status', 'Please verify your email address before accessing the admin panel.');

            // Redirect to login page
            redirect()->to(Filament::getLoginUrl())->send();
            exit;
        }

        // Non-FilamentUser check (only abort in non-local environments)
        abort_if(
            ! ($user instanceof FilamentUser) && config('app.env') !== 'local',
            403,
        );
    }

    protected function redirectTo($request): ?string
    {
        return Filament::getLoginUrl();
    }
}
