<?php

namespace App\Actions\Book;

use App\Models\Book;

final class CreateBookAction
{
    /**
     * @param  array{
     *     title: string,
     *     author: string,
     *     genre: string,
     *     description?: ?string,
     *     total_copies: int,
     *     available_copies: int
     * }  $payload
     */
    public function execute(array $payload): Book
    {
        return Book::query()->create([
            'title' => $payload['title'],
            'author' => $payload['author'],
            'genre' => $payload['genre'],
            'description' => $payload['description'] ?? null,
            'total_copies' => $payload['total_copies'],
            'available_copies' => $payload['available_copies'],
        ]);
    }
}
