<?php

namespace App\Actions\Book;

use App\DTO\Book\CreateBookData;
use App\Models\Book;
use App\Support\BookListCache;

final class CreateBookAction
{
    public function __construct(
        private readonly BookListCache $bookListCache,
    ) {}

    public function execute(CreateBookData $data): Book
    {
        $book = Book::query()->create($data->toCreateArray());

        $this->bookListCache->bumpVersion();

        return $book;
    }
}
