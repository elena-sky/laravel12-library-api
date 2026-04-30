<?php

namespace Tests\Unit\Actions\Book;

use App\Actions\Book\ListBooksAction;
use App\DTO\Book\ListBooksFilters;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListBooksActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function baseFilters(array $overrides = []): ListBooksFilters
    {
        $f = array_merge([
            'title' => null,
            'author' => null,
            'genre' => null,
            'available_only' => false,
            'sort_by' => 'title',
            'sort_dir' => 'asc',
            'per_page' => 15,
            'page' => 1,
        ], $overrides);

        return new ListBooksFilters(
            title: $f['title'],
            author: $f['author'],
            genre: $f['genre'],
            availableOnly: (bool) $f['available_only'],
            sortBy: (string) $f['sort_by'],
            sortDir: (string) $f['sort_dir'],
            perPage: (int) $f['per_page'],
            page: (int) $f['page'],
        );
    }

    public function test_filters_by_title_case_insensitively(): void
    {
        Book::factory()->create(['title' => 'Alpha UniqueToken']);
        Book::factory()->create(['title' => 'Beta']);

        $paginator = $this->app->make(ListBooksAction::class)->execute($this->baseFilters([
            'title' => 'uniquetoken',
        ]));

        $this->assertCount(1, $paginator->items());
        $this->assertSame('Alpha UniqueToken', $paginator->items()[0]->title);
    }

    public function test_available_only_excludes_zero_stock(): void
    {
        Book::factory()->create(['title' => 'In Stock', 'available_copies' => 1, 'total_copies' => 1]);
        Book::factory()->unavailable()->create(['title' => 'Gone']);

        $paginator = $this->app->make(ListBooksAction::class)->execute($this->baseFilters([
            'available_only' => true,
        ]));

        $titles = collect($paginator->items())->pluck('title')->all();
        $this->assertContains('In Stock', $titles);
        $this->assertNotContains('Gone', $titles);
    }

    public function test_sorts_by_column_and_direction(): void
    {
        Book::factory()->create(['title' => 'Aardvark']);
        Book::factory()->create(['title' => 'Zebra']);

        $paginator = $this->app->make(ListBooksAction::class)->execute($this->baseFilters([
            'sort_by' => 'title',
            'sort_dir' => 'desc',
        ]));

        $this->assertSame(['Zebra', 'Aardvark'], collect($paginator->items())->pluck('title')->all());
    }

    public function test_respects_per_page(): void
    {
        Book::factory()->count(4)->create();

        $paginator = $this->app->make(ListBooksAction::class)->execute($this->baseFilters([
            'per_page' => 2,
        ]));

        $this->assertCount(2, $paginator->items());
        $this->assertSame(4, $paginator->total());
    }
}
