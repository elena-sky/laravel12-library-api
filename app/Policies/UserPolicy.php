<?php

namespace App\Policies;

use App\Models\User;

/**
 * Any authenticated user may list and manage user records on /users (assignment-only trade-off).
 * Self-service on /user is still limited to the current user by routing, not by stricter policy rules.
 *
 * @see README.md (Users CRUD and trade-off)
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $actor, User $subject): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $actor, User $subject): bool
    {
        return true;
    }

    public function delete(User $actor, User $subject): bool
    {
        return true;
    }
}
