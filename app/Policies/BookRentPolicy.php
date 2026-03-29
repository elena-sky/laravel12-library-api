<?php

namespace App\Policies;

use App\Models\BookRent;
use App\Models\User;

class BookRentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BookRent $bookRent): bool
    {
        return $user->id === $bookRent->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Extend, update progress, finish.
     */
    public function update(User $user, BookRent $bookRent): bool
    {
        return $user->id === $bookRent->user_id;
    }
}
