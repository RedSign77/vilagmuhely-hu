<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;

class InvitationController extends Controller
{
    public function accept(string $token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        // Check expiration
        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);

            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'This invitation has expired.');
        }

        // Check if user already exists
        $existingUser = User::where('email', $invitation->email)->first();

        if ($existingUser) {
            // User exists, just assign role and mark accepted
            $this->assignRoleAndComplete($existingUser, $invitation);

            return redirect()->route('filament.admin.auth.login')
                ->with('success', 'Invitation accepted! You can now log in.');
        }

        // Redirect to registration with pre-filled data
        session([
            'invitation_token' => $token,
            'invitation_name' => $invitation->name,
            'invitation_email' => $invitation->email,
        ]);

        return redirect()->route('filament.admin.auth.register')
            ->with('info', 'Please complete your registration to accept the invitation.');
    }

    protected function assignRoleAndComplete(User $user, Invitation $invitation): void
    {
        $defaultRole = Role::where('slug', config('invitations.default_role_slug'))->first();

        if ($defaultRole && ! $user->roles->contains($defaultRole->id)) {
            $user->roles()->attach($defaultRole);
        }

        $invitation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'accepted_by_user_id' => $user->id,
        ]);
    }
}
