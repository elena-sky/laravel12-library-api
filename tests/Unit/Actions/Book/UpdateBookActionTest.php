<?php

namespace Tests\Unit\Actions\Book;

use App\Actions\Book\UpdateBookAction;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateBookActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_only_given_attributes(): void
    {
        $book = Book::factory()->create([
            'title' => 'Old',
            'author' => 'Same',
            'total_copies' => 10,
            'available_copies' => 10,
        ]);

        $updated = (new UpdateBookAction)->execute($book, ['title' => 'New']);

        $this->assertSame('New', $updated->title);
        $this->assertSame('Same', $updated->author);
        $this->assertSame(10, $updated->total_copies);
    }
}
