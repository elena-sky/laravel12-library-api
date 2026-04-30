<?php

namespace Tests\Unit\Actions\Book;

use App\Actions\Book\CreateBookAction;
use App\DTO\Book\CreateBookData;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateBookActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_book_from_payload(): void
    {
        $book = $this->app->make(CreateBookAction::class)->execute(CreateBookData::fromValidated([
            'title' => 'Dune',
            'author' => 'Herbert',
            'genre' => 'Sci-Fi',
            'description' => 'Sand',
            'total_copies' => 4,
            'available_copies' => 4,
        ]));

        $this->assertInstanceOf(Book::class, $book);
        $this->assertTrue($book->exists);
        $this->assertSame('Dune', $book->title);
        $this->assertSame('Herbert', $book->author);
        $this->assertSame('Sci-Fi', $book->genre);
        $this->assertSame('Sand', $book->description);
        $this->assertSame(4, $book->total_copies);
        $this->assertSame(4, $book->available_copies);
    }

    public function test_omitted_description_is_null(): void
    {
        $book = $this->app->make(CreateBookAction::class)->execute(CreateBookData::fromValidated([
            'title' => 'T',
            'author' => 'A',
            'genre' => 'G',
            'total_copies' => 1,
            'available_copies' => 1,
        ]));

        $this->assertNull($book->description);
    }
}
