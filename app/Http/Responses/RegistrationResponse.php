<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as RegistrationResponseContract;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class RegistrationResponse implements RegistrationResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = auth()->user();

        // Check if user came from invitation (email already verified)
        if ($user && $user->hasVerifiedEmail()) {
            Notification::make()
                ->title('Welcome to Világműhely!')
                ->body('Your invitation has been accepted successfully. Welcome aboard!')
                ->success()
                ->duration(5000)
                ->send();

            // Redirect to admin panel dashboard
            return redirect()->to(Filament::getUrl());
        }

        // Show notification about email verification for regular users
        Notification::make()
            ->title('Registration successful!')
            ->body('Please check your email to verify your account.')
            ->warning()
            ->duration(5000)
            ->send();

        // User stays logged in but can't access panel until verified
        // Redirect to intended URL which will trigger verification check
        return redirect()->intended(Filament::getUrl());
    }
}
