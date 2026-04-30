<?php

namespace App\Actions\Book;

use App\DTO\Book\UpdateBookData;
use App\Models\Book;
use App\Support\BookListCache;

final class UpdateBookAction
{
    public function __construct(
        private readonly BookListCache $bookListCache,
    ) {}

    public function execute(Book $book, UpdateBookData $data): Book
    {
        $book->fill($data->toFillArray());
        $book->save();

        $this->bookListCache->bumpVersion();

        return $book->refresh();
    }
}
