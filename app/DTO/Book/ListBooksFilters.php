<?php

namespace App\DTO\Book;

use App\Support\BookListCache;

final readonly class ListBooksFilters
{
    public function __construct(
        public ?string $title,
        public ?string $author,
        public ?string $genre,
        public bool $availableOnly,
        public string $sortBy,
        public string $sortDir,
        public int $perPage,
        public int $page,
    ) {}

    /**
     * Raw filter shape for {@see BookListCache::normalizeListFilters()}.
     *
     * @return array{
     *     title: ?string,
     *     author: ?string,
     *     genre: ?string,
     *     available_only: bool,
     *     sort_by: string,
     *     sort_dir: string,
     *     per_page: int,
     *     page: int
     * }
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'author' => $this->author,
            'genre' => $this->genre,
            'available_only' => $this->availableOnly,
            'sort_by' => $this->sortBy,
            'sort_dir' => $this->sortDir,
            'per_page' => $this->perPage,
            'page' => $this->page,
        ];
    }
}
