<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

/**
 * No admin roles: any authenticated user may manage the catalog (assignment scope).
 */
class BookPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Book $book): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Book $book): bool
    {
        return true;
    }

    public function delete(User $user, Book $book): bool
    {
        return true;
    }
}
