<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use Illuminate\Console\Command;

class CleanupExpiredInvitations extends Command
{
    protected $signature = 'invitations:cleanup';

    protected $description = 'Mark expired invitations and delete old ones';

    public function handle(): void
    {
        // Mark as expired
        $expired = Invitation::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Marked {$expired} invitations as expired");

        // Delete old expired/cancelled invitations
        $deleted = Invitation::whereIn('status', ['expired', 'cancelled'])
            ->where('created_at', '<', now()->subDays(config('invitations.cleanup_after_days', 30)))
            ->delete();

        $this->info("Deleted {$deleted} old invitations");
    }
}
