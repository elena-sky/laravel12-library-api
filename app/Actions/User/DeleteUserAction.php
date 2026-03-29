<?php

namespace App\Actions\User;

use App\Exceptions\ResourceConflictException;
use App\Models\User;

final class DeleteUserAction
{
    /**
     * @throws ResourceConflictException
     */
    public function execute(User $actor, User $target): void
    {
        if ($actor->is($target)) {
            throw new ResourceConflictException('Cannot delete your own account');
        }

        if ($target->bookRents()->exists()) {
            throw new ResourceConflictException('Cannot delete user with rental history');
        }

        $target->delete();
    }
}
