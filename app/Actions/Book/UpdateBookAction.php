<?php

namespace App\Actions\Book;

use App\Models\Book;

final class UpdateBookAction
{
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

        return $book->refresh();
    }
}
