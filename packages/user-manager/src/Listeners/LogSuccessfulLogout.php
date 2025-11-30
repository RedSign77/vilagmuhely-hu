<?php

namespace Webtechsolutions\UserManager\Listeners;

use Illuminate\Auth\Events\Logout;
use Webtechsolutions\UserManager\Models\UserActivityLog;

class LogSuccessfulLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if ($event->user) {
            UserActivityLog::log(
                userId: $event->user->id,
                activityType: UserActivityLog::TYPE_LOGOUT,
                description: 'User logged out successfully'
            );
        }
    }
}
