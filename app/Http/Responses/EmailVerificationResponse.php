<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\EmailVerificationResponse as EmailVerificationResponseContract;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class EmailVerificationResponse implements EmailVerificationResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = $request->user();

        // Ensure email is marked as verified
        if ($user && ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // Show success notification
        Notification::make()
            ->title('Email verified successfully!')
            ->body('Your email has been verified. You can now access the admin panel.')
            ->success()
            ->send();

        return redirect()->intended(Filament::getUrl());
    }
}
