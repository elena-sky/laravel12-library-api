<?php

namespace Tests\Unit\Support;

use App\Support\BookListCache;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BookListCacheTest extends TestCase
{
    private function cache(): BookListCache
    {
        return new BookListCache(300);
    }

    private function baseNormalized(): array
    {
        return [
            'title' => null,
            'author' => null,
            'genre' => null,
            'available_only' => false,
            'sort_by' => 'title',
            'sort_dir' => 'asc',
            'per_page' => 15,
            'page' => 1,
        ];
    }

    public function test_normalize_trims_lowercases_search_strings_and_nulls_empty(): void
    {
        $c = $this->cache();

        $n = $c->normalizeListFilters([
            'title' => '  HARRY  ',
            'author' => null,
            'genre' => '',
            'available_only' => false,
            'sort_by' => 'title',
            'sort_dir' => 'asc',
            'per_page' => 15,
            'page' => 1,
        ]);

        $this->assertSame('harry', $n['title']);
        $this->assertNull($n['author']);
        $this->assertNull($n['genre']);
    }

    public function test_equivalent_title_casing_produces_same_list_key(): void
    {
        Cache::flush();
        $c = $this->cache();

        $a = $c->normalizeListFilters(array_merge($this->baseNormalized(), ['title' => 'UniqueX']));
        $b = $c->normalizeListFilters(array_merge($this->baseNormalized(), ['title' => '  uniquex  ']));

        $this->assertSame($c->makeListKey($a), $c->makeListKey($b));
    }

    public function test_ksort_makes_key_independent_of_array_key_order(): void
    {
        Cache::flush();
        $c = $this->cache();

        $one = [
            'page' => 2,
            'title' => 'a',
            'sort_by' => 'author',
            'sort_dir' => 'desc',
            'per_page' => 10,
            'available_only' => true,
            'author' => null,
            'genre' => null,
        ];
        $two = [
            'title' => 'a',
            'author' => null,
            'genre' => null,
            'available_only' => true,
            'sort_by' => 'author',
            'sort_dir' => 'desc',
            'per_page' => 10,
            'page' => 2,
        ];

        $this->assertSame($c->makeListKey($one), $c->makeListKey($two));
    }

    public function test_different_page_or_sort_changes_key(): void
    {
        Cache::flush();
        $c = $this->cache();

        $base = $c->normalizeListFilters($this->baseNormalized());
        $p2 = $c->normalizeListFilters(array_merge($this->baseNormalized(), ['page' => 2]));
        $sort = $c->normalizeListFilters(array_merge($this->baseNormalized(), ['sort_by' => 'author']));

        $this->assertNotSame($c->makeListKey($base), $c->makeListKey($p2));
        $this->assertNotSame($c->makeListKey($base), $c->makeListKey($sort));
    }

    public function test_bump_version_on_cold_store_is_deterministic(): void
    {
        Cache::flush();
        $c = $this->cache();

        $this->assertSame(1, $c->currentVersion());

        $k1 = $c->makeListKey($this->baseNormalized());
        $c->bumpVersion();
        $this->assertSame(2, $c->currentVersion());
        $k2 = $c->makeListKey($this->baseNormalized());

        $this->assertNotSame($k1, $k2);
    }
}
