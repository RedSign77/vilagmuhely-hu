<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Invitation Expiration
    |--------------------------------------------------------------------------
    |
    | Number of hours before an invitation expires. Default is 72 hours (3 days).
    |
    */
    'expires_in_hours' => env('INVITATION_EXPIRES_IN_HOURS', 72),

    /*
    |--------------------------------------------------------------------------
    | Default Role
    |--------------------------------------------------------------------------
    |
    | The slug of the role to assign to users who accept invitations.
    |
    */
    'default_role_slug' => env('INVITATION_DEFAULT_ROLE', 'members'),

    /*
    |--------------------------------------------------------------------------
    | Cleanup After Days
    |--------------------------------------------------------------------------
    |
    | Number of days to keep expired/cancelled invitations before cleanup.
    |
    */
    'cleanup_after_days' => env('INVITATION_CLEANUP_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Reminder Hours Before Expiry
    |--------------------------------------------------------------------------
    |
    | Number of hours before expiration to send reminder emails. Default is 24 hours.
    |
    */
    'reminder_hours_before_expiry' => env('INVITATION_REMINDER_HOURS', 24),
];
