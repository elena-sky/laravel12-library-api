<?php

namespace App\Actions\User;

use App\Models\User;

final class UpdateUserPasswordAction
{
    /**
     * @param  array{password: string}  $payload
     */
    public function execute(User $user, array $payload): void
    {
        $user->update([
            'password' => $payload['password'],
        ]);
    }
}
