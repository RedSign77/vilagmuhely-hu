<?php

namespace Webtechsolutions\UserManager\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use Webtechsolutions\UserManager\Models\UserActivityLog;

class LogPasswordChange
{
    /**
     * Handle the event.
     */
    public function handle(PasswordReset $event): void
    {
        if ($event->user) {
            UserActivityLog::log(
                userId: $event->user->id,
                activityType: UserActivityLog::TYPE_PASSWORD_CHANGE,
                description: 'User password was reset/changed'
            );
        }
    }
}
