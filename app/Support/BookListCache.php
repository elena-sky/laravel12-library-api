<?php

namespace App\Support;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

final class BookListCache
{
    public const string VERSION_KEY = 'books:index:version';

    private const string LIST_KEY_PREFIX = 'books:index';

    public function __construct(
        private readonly int $listTtlSeconds,
    ) {}

    public static function fromConfig(): self
    {
        return new self((int) config('library.book_list_cache_ttl', 300));
    }

    public function listTtlSeconds(): int
    {
        return $this->listTtlSeconds;
    }

    public function currentVersion(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    /**
     * Normalize list filters for cache keys and for querying (same semantics).
     *
     * @param  array<string, mixed>  $raw
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
    public function normalizeListFilters(array $raw): array
    {
        return [
            'title' => $this->normalizeSearchString($raw['title'] ?? null),
            'author' => $this->normalizeSearchString($raw['author'] ?? null),
            'genre' => $this->normalizeSearchString($raw['genre'] ?? null),
            'available_only' => (bool) ($raw['available_only'] ?? false),
            'sort_by' => (string) $raw['sort_by'],
            'sort_dir' => (string) $raw['sort_dir'],
            'per_page' => (int) $raw['per_page'],
            'page' => (int) $raw['page'],
        ];
    }

    /**
     * @param  array{
     *     title: ?string,
     *     author: ?string,
     *     genre: ?string,
     *     available_only: bool,
     *     sort_by: string,
     *     sort_dir: string,
     *     per_page: int,
     *     page: int
     * }  $normalizedFilters
     */
    public function makeListKey(array $normalizedFilters): string
    {
        $sorted = $normalizedFilters;
        ksort($sorted);
        $payload = json_encode($sorted, JSON_THROW_ON_ERROR);

        return self::LIST_KEY_PREFIX.':v'.$this->currentVersion().':'.hash('xxh128', $payload);
    }

    /**
     * @param  array{
     *     title: ?string,
     *     author: ?string,
     *     genre: ?string,
     *     available_only: bool,
     *     sort_by: string,
     *     sort_dir: string,
     *     per_page: int,
     *     page: int
     * }  $normalizedFilters
     */
    public function remember(array $normalizedFilters, Closure $resolver): LengthAwarePaginator
    {
        $key = $this->makeListKey($normalizedFilters);

        /** @var LengthAwarePaginator */
        return Cache::remember($key, $this->listTtlSeconds, $resolver);
    }

    public function bumpVersion(): void
    {
        $next = $this->currentVersion() + 1;
        Cache::forever(self::VERSION_KEY, $next);
    }

    private function normalizeSearchString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }

        return mb_strtolower($s);
    }
}
