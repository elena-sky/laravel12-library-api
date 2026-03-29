<?php

namespace Tests\Unit\Actions\Book;

use App\Actions\Book\DeleteBookAction;
use App\Enums\BookRentStatus;
use App\Exceptions\ResourceConflictException;
use App\Models\Book;
use App\Models\BookRent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteBookActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_book_when_no_active_rents(): void
    {
        $book = Book::factory()->create();
        BookRent::factory()->for($book)->finished()->create();

        (new DeleteBookAction)->execute($book);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_throws_when_active_rent_exists(): void
    {
        $book = Book::factory()->create();
        BookRent::factory()->for($book)->create(['status' => BookRentStatus::Active]);

        $this->expectException(ResourceConflictException::class);
        $this->expectExceptionMessage('Cannot delete book with active rentals');

        (new DeleteBookAction)->execute($book);
    }
}
