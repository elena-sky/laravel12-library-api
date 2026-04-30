<?php

namespace App\Actions\User;

use App\DTO\User\UpdateUserData;
use App\Models\User;

final class UpdateUserAction
{
    public function execute(User $user, UpdateUserData $data): User
    {
        if ($data->isEmpty()) {
            return $user;
        }

        $user->update($data->toUpdateArray());
        $user->refresh();

        return $user;
    }
}
