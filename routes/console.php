<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('emails:process-scheduled')
    ->everyMinute()
    ->withoutOverlapping();

// Invitation management
Schedule::command('invitations:cleanup')
    ->daily()
    ->at('01:00');

Schedule::command('invitations:send-reminders')
    ->daily()
    ->at('09:00');
