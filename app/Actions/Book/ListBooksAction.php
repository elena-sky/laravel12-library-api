<?php

namespace App\Actions\Book;

use App\Models\Book;
use App\Support\BookListCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListBooksAction
{
    /** @var list<string> */
    public const array SORT_WHITELIST = [
        'title',
        'author',
        'genre',
        'created_at',
        'available_copies',
        'total_copies',
    ];

    public function __construct(
        private readonly BookListCache $bookListCache,
    ) {}

    /**
     * @param  array{
     *     title?: ?string,
     *     author?: ?string,
     *     genre?: ?string,
     *     available_only?: bool,
     *     sort_by: string,
     *     sort_dir: string,
     *     per_page: int,
     *     page: int
     * }  $filters
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        $normalized = $this->bookListCache->normalizeListFilters($filters);

        return $this->bookListCache->remember($normalized, function () use ($normalized): LengthAwarePaginator {
            $query = Book::query();

            $this->applyFilters($query, $normalized);

            $sortBy = $normalized['sort_by'];
            $sortDir = $normalized['sort_dir'];
            $query->orderBy($sortBy, $sortDir);

            return $query->paginate(
                $normalized['per_page'],
                ['*'],
                'page',
                $normalized['page']
            );
        });
    }

    /**
     * @param  Builder<Book>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['title'])) {
            $term = '%'.(string) $filters['title'].'%';
            $query->whereRaw('LOWER(title) LIKE ?', [$term]);
        }

        if (! empty($filters['author'])) {
            $term = '%'.(string) $filters['author'].'%';
            $query->whereRaw('LOWER(author) LIKE ?', [$term]);
        }

        if (! empty($filters['genre'])) {
            $term = '%'.(string) $filters['genre'].'%';
            $query->whereRaw('LOWER(genre) LIKE ?', [$term]);
        }

        if (! empty($filters['available_only'])) {
            $query->where('available_copies', '>', 0);
        }
    }
}
