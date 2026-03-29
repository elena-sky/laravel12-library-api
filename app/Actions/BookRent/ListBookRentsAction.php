<?php

namespace App\Actions\BookRent;

use App\Models\BookRent;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListBookRentsAction
{
    public function execute(User $user, int $perPage): LengthAwarePaginator
    {
        return BookRent::query()
            ->where('user_id', $user->id)
            ->with('book')
            ->latest('rented_at')
            ->paginate($perPage);
    }
}
