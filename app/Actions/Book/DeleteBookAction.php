<?php

namespace App\Actions\Book;

use App\Enums\BookRentStatus;
use App\Exceptions\ResourceConflictException;
use App\Models\Book;

final class DeleteBookAction
{
    /**
     * @throws ResourceConflictException
     */
    public function execute(Book $book): void
    {
        $hasActive = $book->bookRents()
            ->where('status', BookRentStatus::Active)
            ->exists();

        if ($hasActive) {
            throw new ResourceConflictException('Cannot delete book with active rentals');
        }

        $book->delete();
    }
}
