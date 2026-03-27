<?php

namespace App\Actions\User;

use App\Models\User;

final class CreateUserAction
{
    /**
     * @param  array{name: string, email: string, password: string}  $payload
     */
    public function execute(array $payload): User
    {
        return User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => $payload['password'],
        ]);
    }
}
