<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    public function __invoke(Request $request, string $id, string $hash)
    {
        // Validate signature manually
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired verification link.');
        }

        // Find user by ID
        $user = User::findOrFail($id);

        // Verify hash matches
        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            Notification::make()
                ->title('Email Already Verified')
                ->body('Your email has already been verified.')
                ->info()
                ->send();

            // Login user if not authenticated
            if (! Auth::check()) {
                Auth::login($user);
            }

            return redirect()->to(Filament::getUrl());
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        // Dispatch Verified event
        event(new Verified($user));

        // Auto-login the user
        Auth::login($user);

        // Show success notification
        Notification::make()
            ->title('Email Verified Successfully!')
            ->body('Your email has been verified. Welcome!')
            ->success()
            ->send();

        // Redirect to admin panel
        return redirect()->to(Filament::getUrl());
    }
}
