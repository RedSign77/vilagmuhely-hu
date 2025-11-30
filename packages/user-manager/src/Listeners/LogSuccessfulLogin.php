<?php

namespace Webtechsolutions\UserManager\Listeners;

use Illuminate\Auth\Events\Login;
use Webtechsolutions\UserManager\Models\UserActivityLog;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        if ($event->user) {
            UserActivityLog::log(
                userId: $event->user->id,
                activityType: UserActivityLog::TYPE_LOGIN,
                description: 'User logged in successfully'
            );
        }
    }
}
