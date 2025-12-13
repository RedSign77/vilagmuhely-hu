<?php

namespace Webtechsolutions\UserManager\Listeners;

use Illuminate\Auth\Events\Failed;
use Webtechsolutions\UserManager\Models\UserActivityLog;

class LogFailedLogin
{
    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        UserActivityLog::log(
            userId: $event->user?->id,
            activityType: UserActivityLog::TYPE_FAILED_LOGIN,
            description: 'Failed login attempt for '.($event->credentials['email'] ?? 'unknown'),
            properties: [
                'email' => $event->credentials['email'] ?? null,
            ]
        );
    }
}
