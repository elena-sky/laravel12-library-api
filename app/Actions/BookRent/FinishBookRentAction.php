<?php

namespace App\Actions\BookRent;

use App\Enums\BookRentStatus;
use App\Exceptions\ResourceConflictException;
use App\Models\Book;
use App\Models\BookRent;
use Illuminate\Support\Facades\DB;

final class FinishBookRentAction
{
    /**
     * @throws ResourceConflictException
     */
    public function execute(BookRent $rent): BookRent
    {
        return DB::transaction(function () use ($rent): BookRent {
            /** @var BookRent $lockedRent */
            $lockedRent = BookRent::query()->whereKey($rent->getKey())->lockForUpdate()->firstOrFail();

            if (! $lockedRent->isActive()) {
                throw new ResourceConflictException('Rental is already finished');
            }

            /** @var Book $lockedBook */
            $lockedBook = Book::query()->whereKey($lockedRent->book_id)->lockForUpdate()->firstOrFail();

            $lockedRent->status = BookRentStatus::Finished;
            $lockedRent->returned_at = now();
            $lockedRent->save();

            $lockedBook->available_copies++;
            $lockedBook->save();

            return $lockedRent->refresh();
        });
    }
}
