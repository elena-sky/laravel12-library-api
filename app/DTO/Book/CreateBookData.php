<?php

namespace App\DTO\Book;

final readonly class CreateBookData
{
    public function __construct(
        public string $title,
        public string $author,
        public string $genre,
        public ?string $description,
        public int $totalCopies,
        public int $availableCopies,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            title: (string) $validated['title'],
            author: (string) $validated['author'],
            genre: (string) $validated['genre'],
            description: array_key_exists('description', $validated) && $validated['description'] !== null
                ? (string) $validated['description']
                : null,
            totalCopies: (int) $validated['total_copies'],
            availableCopies: (int) $validated['available_copies'],
        );
    }

    /**
     * @return array{title: string, author: string, genre: string, description: ?string, total_copies: int, available_copies: int}
     */
    public function toCreateArray(): array
    {
        return [
            'title' => $this->title,
            'author' => $this->author,
            'genre' => $this->genre,
            'description' => $this->description,
            'total_copies' => $this->totalCopies,
            'available_copies' => $this->availableCopies,
        ];
    }
}
