<?php

namespace App\Actions\Book;

use App\Models\Book;
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

    /**
     * @param  array{
     *     title?: ?string,
     *     author?: ?string,
     *     genre?: ?string,
     *     available_only?: bool,
     *     sort_by: string,
     *     sort_dir: string,
     *     per_page: int
     * }  $filters
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        $query = Book::query();

        $this->applyFilters($query, $filters);

        $sortBy = $filters['sort_by'];
        $sortDir = $filters['sort_dir'];
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($filters['per_page']);
    }

    /**
     * @param  Builder<Book>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['title'])) {
            $term = '%'.mb_strtolower((string) $filters['title']).'%';
            $query->whereRaw('LOWER(title) LIKE ?', [$term]);
        }

        if (! empty($filters['author'])) {
            $term = '%'.mb_strtolower((string) $filters['author']).'%';
            $query->whereRaw('LOWER(author) LIKE ?', [$term]);
        }

        if (! empty($filters['genre'])) {
            $term = '%'.mb_strtolower((string) $filters['genre']).'%';
            $query->whereRaw('LOWER(genre) LIKE ?', [$term]);
        }

        if (! empty($filters['available_only'])) {
            $query->where('available_copies', '>', 0);
        }
    }
}
