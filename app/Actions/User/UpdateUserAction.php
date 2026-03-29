<?php

namespace App\Actions\User;

use App\Models\User;

final class UpdateUserAction
{
    /**
     * @param  array{name?: string, email?: string}  $payload
     */
    public function execute(User $user, array $payload): User
    {
        $data = [];

        if (array_key_exists('name', $payload)) {
            $data['name'] = $payload['name'];
        }

        if (array_key_exists('email', $payload)) {
            $data['email'] = $payload['email'];
        }

        if ($data === []) {
            return $user;
        }

        $user->update($data);
        $user->refresh();

        return $user;
    }
}
