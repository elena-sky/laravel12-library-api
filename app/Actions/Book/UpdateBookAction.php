<?php

namespace App\Actions\Book;

use App\Models\Book;
use App\Support\BookListCache;

final class UpdateBookAction
{
    public function __construct(
        private readonly BookListCache $bookListCache,
    ) {}

    /**
     * @param  array{
     *     title?: string,
     *     author?: string,
     *     genre?: string,
     *     description?: ?string,
     *     total_copies?: int,
     *     available_copies?: int
     * }  $payload
     */
    public function execute(Book $book, array $payload): Book
    {
        $book->fill($payload);
        $book->save();

        $this->bookListCache->bumpVersion();

        return $book->refresh();
    }
}
