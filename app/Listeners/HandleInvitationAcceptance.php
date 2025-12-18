<?php

namespace App\Listeners;

use App\Models\Invitation;
use App\Models\Role;
use Illuminate\Auth\Events\Registered;

class HandleInvitationAcceptance
{
    public function handle(Registered $event): void
    {
        if ($token = session('invitation_token')) {
            $invitation = Invitation::where('token', $token)
                ->where('email', $event->user->email)
                ->where('status', 'pending')
                ->first();

            if ($invitation && ! $invitation->isExpired()) {
                $defaultRole = Role::where('slug', config('invitations.default_role_slug'))->first();

                if ($defaultRole) {
                    $event->user->roles()->attach($defaultRole);
                }

                $invitation->update([
                    'status' => 'accepted',
                    'accepted_at' => now(),
                    'accepted_by_user_id' => $event->user->id,
                ]);
            }

            session()->forget(['invitation_token', 'invitation_name', 'invitation_email']);
        }
    }
}
