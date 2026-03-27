<?php

namespace App\Actions\User;

use App\Models\User;

final class UpdateUserAction
{
    /**
     * @param  array{name: string, email: string}  $payload
     */
    public function execute(User $user, array $payload): User
    {
        $user->update([
            'name' => $payload['name'],
            'email' => $payload['email'],
        ]);

        $user->refresh();

        return $user;
    }
}
