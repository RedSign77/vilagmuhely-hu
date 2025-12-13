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
        // Show notification about email verification
        Notification::make()
            ->title('Registration successful!')
            ->body('Please check your email to verify your account before logging in.')
            ->success()
            ->send();

        // Redirect to login page
        return redirect()->to(Filament::getLoginUrl());
    }
}
