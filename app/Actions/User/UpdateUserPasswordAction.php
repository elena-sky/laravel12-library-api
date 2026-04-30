<?php

namespace App\Actions\User;

use App\DTO\User\UpdateUserPasswordData;
use App\Models\User;

final class UpdateUserPasswordAction
{
    public function execute(User $user, UpdateUserPasswordData $data): void
    {
        $user->update([
            'password' => $data->password,
        ]);
    }
}
