<?php

namespace App\Actions\User;

use App\DTO\User\CreateUserData;
use App\Models\User;

final class CreateUserAction
{
    public function execute(CreateUserData $data): User
    {
        return User::create($data->toCreateArray());
    }
}
