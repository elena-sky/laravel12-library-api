<?php

namespace App\Actions\User;

use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class LoginUserAction
{
    /**
     * @throws ApiException When credentials are invalid (401, neutral message).
     */
    public function execute(string $email, string $password): User
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw new ApiException('Invalid credentials', 401);
        }

        return $user;
    }
}
