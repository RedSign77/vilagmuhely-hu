<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;

class InvitationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSupervisor();
    }

    public function view(User $user, Invitation $invitation): bool
    {
        return $user->isSupervisor();
    }

    public function create(User $user): bool
    {
        return $user->isSupervisor();
    }

    public function update(User $user, Invitation $invitation): bool
    {
        return $user->isSupervisor();
    }

    public function delete(User $user, Invitation $invitation): bool
    {
        return $user->isSupervisor();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isSupervisor();
    }
}
