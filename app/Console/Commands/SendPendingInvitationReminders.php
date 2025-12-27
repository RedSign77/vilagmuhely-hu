<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use App\Notifications\InvitationNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendPendingInvitationReminders extends Command
{
    protected $signature = 'invitations:send-reminders';

    protected $description = 'Send reminder emails for pending invitations that are expiring soon';

    public function handle(): int
    {
        $hoursBeforeExpiry = config('invitations.reminder_hours_before_expiry', 24);

        // Get pending invitations that expire within the configured timeframe
        // and haven't been reminded recently
        $invitations = Invitation::where('status', 'pending')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addHours($hoursBeforeExpiry))
            ->whereNull('reminded_at')
            ->get();

        if ($invitations->isEmpty()) {
            $this->info('No pending invitations to remind.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($invitations as $invitation) {
            Notification::route('mail', $invitation->email)
                ->notify(new InvitationNotification($invitation, true));

            // Mark as reminded
            $invitation->update(['reminded_at' => now()]);
            $count++;
        }

        $this->info("Sent {$count} invitation reminders.");

        return Command::SUCCESS;
    }
}
