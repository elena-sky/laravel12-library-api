<?php

namespace App\Policies;

use App\Models\User;

/**
 * Self-service account rules; no admin or role matrix until explicitly introduced.
 */
class UserPolicy
{
    public function view(User $actor, User $subject): bool
    {
        return $actor->is($subject);
    }

    public function update(User $actor, User $subject): bool
    {
        return $actor->is($subject);
    }
}
