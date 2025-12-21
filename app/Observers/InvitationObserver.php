<?php

namespace App\Observers;

use App\Models\CrystalActivityQueue;
use App\Models\Invitation;

class InvitationObserver
{
    /**
     * Handle the Invitation "created" event.
     */
    public function created(Invitation $invitation): void
    {
        // Queue crystal update for the inviter
        CrystalActivityQueue::addActivity(
            userId: $invitation->invited_by_user_id,
            activityType: CrystalActivityQueue::TYPE_INVITATION_SENT,
            metadata: [
                'invitation_id' => $invitation->id,
                'email' => $invitation->email,
            ]
        );
    }

    /**
     * Handle the Invitation "updated" event.
     */
    public function updated(Invitation $invitation): void
    {
        // Check if invitation was just accepted
        if ($invitation->isDirty('status') && $invitation->status === 'accepted') {
            // Queue crystal update for the inviter
            CrystalActivityQueue::addActivity(
                userId: $invitation->invited_by_user_id,
                activityType: CrystalActivityQueue::TYPE_INVITATION_ACCEPTED,
                metadata: [
                    'invitation_id' => $invitation->id,
                    'accepted_by_user_id' => $invitation->accepted_by_user_id,
                    'is_inviter' => true,
                ]
            );

            // Queue crystal update for the invited user (who accepted)
            if ($invitation->accepted_by_user_id) {
                CrystalActivityQueue::addActivity(
                    userId: $invitation->accepted_by_user_id,
                    activityType: CrystalActivityQueue::TYPE_INVITATION_ACCEPTED,
                    metadata: [
                        'invitation_id' => $invitation->id,
                        'invited_by_user_id' => $invitation->invited_by_user_id,
                        'is_invited' => true,
                    ]
                );
            }
        }
    }
}
