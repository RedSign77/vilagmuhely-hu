<?php

namespace Webtechsolutions\UserManager\Observers;

use App\Models\User;
use Webtechsolutions\UserManager\Models\Role;
use Webtechsolutions\UserManager\Models\UserActivityLog;

class UserObserver
{
    /**
     * Track which attributes we care about for profile changes
     */
    protected array $profileAttributes = [
        'name',
        'email',
        'avatar',
        'mobile',
        'city',
        'address',
        'social_media_links',
        'about',
    ];

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Assign the Guest role to newly created users
        $guestRole = Role::where('slug', 'guests')->first();

        if ($guestRole) {
            $user->roles()->attach($guestRole->id);

            UserActivityLog::log(
                userId: $user->id,
                activityType: UserActivityLog::TYPE_ROLE_CHANGE,
                description: 'Guest role automatically assigned to new user',
                properties: [
                    'added_role_ids' => [$guestRole->id],
                    'removed_role_ids' => [],
                    'current_role_ids' => [$guestRole->id],
                ]
            );
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if password was changed
        if ($user->wasChanged('password')) {
            UserActivityLog::log(
                userId: $user->id,
                activityType: UserActivityLog::TYPE_PASSWORD_CHANGE,
                description: 'User password was changed via profile update'
            );
        }

        // Check if profile fields were changed
        $changedProfileFields = array_intersect(
            array_keys($user->getChanges()),
            $this->profileAttributes
        );

        if (! empty($changedProfileFields)) {
            $changes = [];
            foreach ($changedProfileFields as $field) {
                $changes[$field] = [
                    'old' => $user->getOriginal($field),
                    'new' => $user->getAttribute($field),
                ];
            }

            UserActivityLog::log(
                userId: $user->id,
                activityType: UserActivityLog::TYPE_PROFILE_CHANGE,
                description: 'User profile was updated: '.implode(', ', $changedProfileFields),
                properties: ['changes' => $changes]
            );
        }
    }

    /**
     * Handle role changes (called manually when roles are synced)
     */
    public static function logRoleChange(User $user, array $oldRoleIds, array $newRoleIds): void
    {
        $added = array_diff($newRoleIds, $oldRoleIds);
        $removed = array_diff($oldRoleIds, $newRoleIds);

        if (! empty($added) || ! empty($removed)) {
            UserActivityLog::log(
                userId: $user->id,
                activityType: UserActivityLog::TYPE_ROLE_CHANGE,
                description: 'User roles were modified',
                properties: [
                    'added_role_ids' => array_values($added),
                    'removed_role_ids' => array_values($removed),
                    'current_role_ids' => $newRoleIds,
                ]
            );
        }
    }
}
