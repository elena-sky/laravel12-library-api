<?php

namespace Tests\Feature\Api;

use App\Enums\BookRentStatus;
use App\Models\Book;
use App\Models\BookRent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCatalogTest extends TestCase
{
    use RefreshDatabase;

    private const string SAFE_PASSWORD = 'Correct-Horse-Battery-Staple-99';

    private function actingUser(): User
    {
        return User::factory()->create();
    }

    public function test_authenticated_user_can_create_book(): void
    {
        $user = $this->actingUser();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/books', [
            'title' => 'Dune',
            'author' => 'Frank Herbert',
            'genre' => 'Sci-Fi',
            'description' => 'Desert planet',
            'total_copies' => 5,
            'available_copies' => 5,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Dune')
            ->assertJsonPath('data.available_copies', 5);

        $this->assertDatabaseHas('books', [
            'title' => 'Dune',
            'total_copies' => 5,
            'available_copies' => 5,
        ]);
    }

    public function test_create_book_defaults_available_copies_to_total(): void
    {
        $user = $this->actingUser();

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/books', [
            'title' => '1984',
            'author' => 'Orwell',
            'genre' => 'Fiction',
            'total_copies' => 3,
        ])->assertCreated()
            ->assertJsonPath('data.available_copies', 3);
    }

    public function test_authenticated_user_can_update_book(): void
    {
        $user = $this->actingUser();
        $book = Book::factory()->create(['title' => 'Old']);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/books/'.$book->id, ['title' => 'New Title'])
            ->assertOk()
            ->assertJsonPath('data.title', 'New Title');

        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'New Title']);
    }

    public function test_authenticated_user_can_delete_book_without_active_rents(): void
    {
        $user = $this->actingUser();
        $book = Book::factory()->create();
        BookRent::factory()->for($book)->finished()->create();

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/books/'.$book->id)
            ->assertOk()
            ->assertJsonPath('message', 'Book deleted');

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $this->assertDatabaseMissing('book_rents', ['book_id' => $book->id]);
    }

    public function test_cannot_delete_book_with_active_rent(): void
    {
        $user = $this->actingUser();
        $book = Book::factory()->create();
        BookRent::factory()->for($book)->create(['status' => BookRentStatus::Active]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/books/'.$book->id)
            ->assertStatus(409)
            ->assertJsonPath('message', 'Cannot delete book with active rentals');

        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_search_books_by_title_author_genre(): void
    {
        $user = $this->actingUser();
        Book::factory()->create(['title' => 'Alpha Unique', 'author' => 'Smith', 'genre' => 'A']);
        Book::factory()->create(['title' => 'Beta', 'author' => 'Jones Unique', 'genre' => 'B']);
        Book::factory()->create(['title' => 'Gamma', 'author' => 'Brown', 'genre' => 'UniqueGenre']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books?title='.rawurlencode('unique'))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books?author='.rawurlencode('unique'))
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books?genre='.rawurlencode('uniquegenre'))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_filter_available_only(): void
    {
        $user = $this->actingUser();
        Book::factory()->create(['title' => 'In Stock', 'available_copies' => 2, 'total_copies' => 2]);
        Book::factory()->unavailable()->create(['title' => 'Out']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books?available_only=1')
            ->assertOk();

        $titles = collect($response->json('data'))->pluck('title')->all();
        $this->assertContains('In Stock', $titles);
        $this->assertNotContains('Out', $titles);
    }

    public function test_sorting_by_title_desc(): void
    {
        $user = $this->actingUser();
        Book::factory()->create(['title' => 'Aardvark']);
        Book::factory()->create(['title' => 'Zebra']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books?sort_by=title&sort_dir=desc')
            ->assertOk();

        $titles = collect($response->json('data'))->pluck('title')->all();
        $this->assertSame(['Zebra', 'Aardvark'], $titles);
    }

    public function test_list_books_default_sort_is_title_asc_when_sort_omitted(): void
    {
        $user = $this->actingUser();
        Book::factory()->create(['title' => 'Zebra']);
        Book::factory()->create(['title' => 'Aardvark']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books')
            ->assertOk();

        $titles = collect($response->json('data'))->pluck('title')->all();
        $this->assertSame(['Aardvark', 'Zebra'], $titles);
    }

    public function test_list_books_respects_per_page_in_meta(): void
    {
        $user = $this->actingUser();
        Book::factory()->count(4)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books?per_page=2')
            ->assertOk();

        $response->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 4)
            ->assertJsonPath('meta.last_page', 2);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_list_books_default_per_page_is_fifteen_in_meta(): void
    {
        $user = $this->actingUser();
        Book::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 15);
    }

    public function test_guest_cannot_list_books(): void
    {
        $this->getJson('/api/v1/books')->assertUnauthorized();
    }

    public function test_guest_cannot_create_book(): void
    {
        $this->postJson('/api/v1/books', [
            'title' => 'T',
            'author' => 'A',
            'genre' => 'G',
            'total_copies' => 1,
        ])->assertUnauthorized();
    }

    public function test_guest_cannot_show_book(): void
    {
        $book = Book::factory()->create();

        $this->getJson('/api/v1/books/'.$book->id)->assertUnauthorized();
    }

    public function test_guest_cannot_update_book(): void
    {
        $book = Book::factory()->create();

        $this->patchJson('/api/v1/books/'.$book->id, ['title' => 'X'])
            ->assertUnauthorized();
    }

    public function test_guest_cannot_delete_book(): void
    {
        $book = Book::factory()->create();

        $this->deleteJson('/api/v1/books/'.$book->id)
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_show_book(): void
    {
        $user = $this->actingUser();
        $book = Book::factory()->create(['title' => 'Visible']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books/'.$book->id)
            ->assertOk()
            ->assertJsonPath('data.id', $book->id)
            ->assertJsonPath('data.title', 'Visible');
    }

    public function test_store_rejects_available_copies_above_total(): void
    {
        $user = $this->actingUser();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/books', [
                'title' => 'T',
                'author' => 'A',
                'genre' => 'G',
                'total_copies' => 2,
                'available_copies' => 5,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['available_copies']);
    }

    public function test_list_books_rejects_invalid_sort_by(): void
    {
        $user = $this->actingUser();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books?sort_by=not_a_real_column')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort_by']);
    }

    public function test_list_books_rejects_invalid_sort_dir(): void
    {
        $user = $this->actingUser();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books?sort_dir=invalid')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort_dir']);
    }

    public function test_list_books_rejects_per_page_out_of_range(): void
    {
        $user = $this->actingUser();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books?per_page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books?per_page=101')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_show_book_returns_404_for_unknown_id(): void
    {
        $user = $this->actingUser();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/books/999999')
            ->assertNotFound()
            ->assertJson(['message' => 'Resource not found']);
    }

    public function test_update_book_returns_404_for_unknown_id(): void
    {
        $user = $this->actingUser();

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/books/999999', ['title' => 'Nope'])
            ->assertNotFound()
            ->assertJson(['message' => 'Resource not found']);
    }

    public function test_delete_book_returns_404_for_unknown_id(): void
    {
        $user = $this->actingUser();

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/v1/books/999999')
            ->assertNotFound()
            ->assertJson(['message' => 'Resource not found']);
    }

    public function test_update_rejects_available_copies_above_total(): void
    {
        $user = $this->actingUser();
        $book = Book::factory()->create(['total_copies' => 10, 'available_copies' => 5]);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/books/'.$book->id, [
                'available_copies' => 20,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['available_copies']);
    }

    public function test_update_rejects_total_below_current_available_without_adjusting_available(): void
    {
        $user = $this->actingUser();
        $book = Book::factory()->create(['total_copies' => 10, 'available_copies' => 8]);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/books/'.$book->id, [
                'total_copies' => 5,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['total_copies']);
    }
}
