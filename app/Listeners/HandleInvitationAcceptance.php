<?php

namespace App\Listeners;

use App\Models\Invitation;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;

class HandleInvitationAcceptance
{
    public function handle(Registered $event): void
    {
        $token = session('invitation_token');

        if (! $token) {
            return;
        }

        $invitation = Invitation::where('token', $token)
            ->where('email', $event->user->email)
            ->where('status', 'pending')
            ->first();

        if (! $invitation || $invitation->isExpired()) {
            session()->forget(['invitation_token', 'invitation_name', 'invitation_email']);

            return;
        }

        // Assign default role
        $defaultRole = Role::where('slug', config('invitations.default_role_slug'))->first();

        if ($defaultRole) {
            $event->user->roles()->syncWithoutDetaching([$defaultRole->id]);
        }

        // Mark email as verified for invited users
        $event->user->email_verified_at = now();
        $event->user->save();

        // Update invitation record
        $invitation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'accepted_by_user_id' => $event->user->id,
        ]);

        session()->forget(['invitation_token', 'invitation_name', 'invitation_email']);
    }
}
