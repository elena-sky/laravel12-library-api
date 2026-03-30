<?php

namespace App\Actions\Book;

use App\Enums\BookRentStatus;
use App\Exceptions\ResourceConflictException;
use App\Models\Book;
use App\Support\BookListCache;

final class DeleteBookAction
{
    public function __construct(
        private readonly BookListCache $bookListCache,
    ) {}

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

        $this->bookListCache->bumpVersion();
    }
}
