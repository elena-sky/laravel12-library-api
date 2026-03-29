<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListUsersAction
{
    public function execute(int $perPage): LengthAwarePaginator
    {
        return User::query()->orderBy('id')->paginate($perPage);
    }
}
