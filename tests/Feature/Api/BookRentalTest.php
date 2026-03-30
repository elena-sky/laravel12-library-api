<?php

namespace Tests\Feature\Api;

use App\Enums\BookRentStatus;
use App\Models\Book;
use App\Models\BookRent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookRentalTest extends TestCase
{
    use RefreshDatabase;

    private function dueSoon(): string
    {
        return Carbon::now()->addDays(14)->toIso8601String();
    }

    public function test_user_can_rent_available_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 2, 'total_copies' => 2]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/rentals', [
            'book_id' => $book->id,
            'due_date' => $this->dueSoon(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.reading_progress', 0);

        $book->refresh();
        $this->assertSame(1, $book->available_copies);
        $this->assertDatabaseHas('book_rents', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => BookRentStatus::Active->value,
        ]);
    }

    public function test_cannot_rent_unavailable_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->unavailable()->create();

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/rentals', [
            'book_id' => $book->id,
            'due_date' => $this->dueSoon(),
        ])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Book is not available for rent');

        $book->refresh();
        $this->assertSame(0, $book->available_copies);
    }

    public function test_rent_with_nonexistent_book_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/rentals', [
            'book_id' => 99_999,
            'due_date' => $this->dueSoon(),
        ])
            ->assertNotFound()
            ->assertJson(['message' => 'Resource not found']);
    }

    public function test_create_rental_requires_book_id_and_due_date(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/rentals', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['book_id', 'due_date']);
    }

    public function test_create_rental_rejects_due_date_in_past(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 1, 'total_copies' => 1]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/rentals', [
                'book_id' => $book->id,
                'due_date' => Carbon::now()->subDay()->toIso8601String(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['due_date']);
    }

    public function test_user_can_extend_active_rent(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 0, 'total_copies' => 1]);
        $rent = BookRent::factory()->for($user)->for($book)->create([
            'status' => BookRentStatus::Active,
            'extended_count' => 0,
        ]);

        $newDue = Carbon::now()->addDays(30)->toIso8601String();

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/rentals/'.$rent->id.'/extend', ['due_date' => $newDue])
            ->assertOk()
            ->assertJsonPath('data.extended_count', 1);

        $rent->refresh();
        $this->assertSame(1, $rent->extended_count);
    }

    public function test_cannot_extend_finished_rent(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $rent = BookRent::factory()->for($user)->for($book)->finished()->create();

        $newDue = Carbon::now()->addDays(30)->toIso8601String();

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/rentals/'.$rent->id.'/extend', ['due_date' => $newDue])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Cannot extend a finished rental');
    }

    public function test_extend_rejects_due_date_in_past(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 0, 'total_copies' => 1]);
        $rent = BookRent::factory()->for($user)->for($book)->create([
            'status' => BookRentStatus::Active,
        ]);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/rentals/'.$rent->id.'/extend', [
                'due_date' => Carbon::now()->subHour()->toIso8601String(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['due_date']);
    }

    public function test_user_can_update_reading_progress(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 0, 'total_copies' => 1]);
        $rent = BookRent::factory()->for($user)->for($book)->create([
            'status' => BookRentStatus::Active,
            'reading_progress' => 10,
        ]);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/rentals/'.$rent->id.'/reading-progress', [
                'reading_progress' => 55,
            ])
            ->assertOk()
            ->assertJsonPath('data.reading_progress', 55);
    }

    public function test_cannot_set_progress_above_100(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 0, 'total_copies' => 1]);
        $rent = BookRent::factory()->for($user)->for($book)->create(['status' => BookRentStatus::Active]);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/rentals/'.$rent->id.'/reading-progress', [
                'reading_progress' => 101,
            ])
            ->assertStatus(422);
    }

    public function test_cannot_update_progress_on_finished_rent(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $rent = BookRent::factory()->for($user)->for($book)->finished()->create();

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/rentals/'.$rent->id.'/reading-progress', [
                'reading_progress' => 50,
            ])
            ->assertStatus(409)
            ->assertJsonPath('message', 'Cannot update reading progress on a finished rental');
    }

    public function test_finish_rent_restores_available_copies(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 0, 'total_copies' => 2]);
        $rent = BookRent::factory()->for($user)->for($book)->create([
            'status' => BookRentStatus::Active,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/rentals/'.$rent->id.'/finish')
            ->assertOk()
            ->assertJsonPath('data.status', 'finished');

        $book->refresh();
        $rent->refresh();
        $this->assertSame(1, $book->available_copies);
        $this->assertSame(BookRentStatus::Finished, $rent->status);
        $this->assertNotNull($rent->returned_at);
    }

    public function test_cannot_finish_already_finished_rent(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $rent = BookRent::factory()->for($user)->for($book)->finished()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/rentals/'.$rent->id.'/finish')
            ->assertStatus(409)
            ->assertJsonPath('message', 'Rental is already finished');
    }

    public function test_cannot_access_another_users_rent(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 0, 'total_copies' => 1]);
        $rent = BookRent::factory()->for($owner)->for($book)->create(['status' => BookRentStatus::Active]);

        $this->actingAs($other, 'sanctum')
            ->getJson('/api/v1/rentals/'.$rent->id)
            ->assertNotFound();
    }

    public function test_cannot_extend_finish_or_touch_reading_progress_on_another_users_rent(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 0, 'total_copies' => 1]);
        $rent = BookRent::factory()->for($owner)->for($book)->create(['status' => BookRentStatus::Active]);
        $due = $this->dueSoon();

        $cases = [
            ['PATCH', '/api/v1/rentals/'.$rent->id.'/extend', ['due_date' => $due]],
            ['GET', '/api/v1/rentals/'.$rent->id.'/reading-progress', []],
            ['PATCH', '/api/v1/rentals/'.$rent->id.'/reading-progress', ['reading_progress' => 10]],
            ['POST', '/api/v1/rentals/'.$rent->id.'/finish', []],
        ];

        foreach ($cases as [$method, $uri, $payload]) {
            $response = match ($method) {
                'GET' => $this->actingAs($other, 'sanctum')->getJson($uri),
                'POST' => $this->actingAs($other, 'sanctum')->postJson($uri, $payload),
                'PATCH' => $this->actingAs($other, 'sanctum')->patchJson($uri, $payload),
                default => throw new \InvalidArgumentException('Unsupported method: '.$method),
            };

            $response->assertNotFound()
                ->assertJson(['message' => 'Resource not found']);
        }
    }

    public function test_reading_progress_endpoint_returns_value(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 0, 'total_copies' => 1]);
        $rent = BookRent::factory()->for($user)->for($book)->create([
            'reading_progress' => 42,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/rentals/'.$rent->id.'/reading-progress')
            ->assertOk()
            ->assertJsonPath('data.reading_progress', 42);
    }

    public function test_user_can_list_own_rentals_ordered_by_latest_rented_at(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $bookAliceOld = Book::factory()->create(['available_copies' => 0, 'total_copies' => 1]);
        $bookAliceNew = Book::factory()->create(['available_copies' => 0, 'total_copies' => 1]);
        $bookBob = Book::factory()->create(['available_copies' => 0, 'total_copies' => 1]);

        $older = BookRent::factory()->for($alice)->for($bookAliceOld)->create([
            'status' => BookRentStatus::Active,
            'rented_at' => Carbon::parse('2020-01-01 12:00:00'),
            'due_date' => Carbon::parse('2020-01-20 12:00:00'),
        ]);
        $newer = BookRent::factory()->for($alice)->for($bookAliceNew)->create([
            'status' => BookRentStatus::Active,
            'rented_at' => Carbon::parse('2025-06-01 12:00:00'),
            'due_date' => Carbon::parse('2025-06-20 12:00:00'),
        ]);
        BookRent::factory()->for($bob)->for($bookBob)->create([
            'status' => BookRentStatus::Active,
        ]);

        $response = $this->actingAs($alice, 'sanctum')->getJson('/api/v1/rentals');

        $response->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonCount(2, 'data');

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertSame([$newer->id, $older->id], $ids);
    }

    public function test_list_rentals_respects_per_page_query(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 3) as $i) {
            $book = Book::factory()->create(['available_copies' => 0, 'total_copies' => 1]);
            BookRent::factory()->for($user)->for($book)->create([
                'status' => BookRentStatus::Active,
                'rented_at' => now()->subDays(10 - $i),
            ]);
        }

        $first = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/rentals?per_page=2')
            ->assertOk();

        $first->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonCount(2, 'data');

        $second = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/rentals?per_page=2&page=2')
            ->assertOk();

        $second->assertJsonPath('meta.current_page', 2)
            ->assertJsonCount(1, 'data');
    }

    public function test_list_rentals_default_per_page_is_fifteen_in_meta(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/rentals')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 15);
    }

    public function test_list_rentals_rejects_per_page_out_of_range(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/rentals?per_page=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/rentals?per_page=101')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_user_can_show_own_rental_with_nested_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'title' => 'Nested Show Title',
            'available_copies' => 0,
            'total_copies' => 1,
        ]);
        $rent = BookRent::factory()->for($user)->for($book)->create([
            'status' => BookRentStatus::Active,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/rentals/'.$rent->id)
            ->assertOk()
            ->assertJsonPath('data.id', $rent->id)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.book.title', 'Nested Show Title')
            ->assertJsonPath('data.book.id', $book->id);
    }

    public function test_guest_cannot_access_protected_rental_routes(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['available_copies' => 1, 'total_copies' => 1]);
        $rent = BookRent::factory()->for($user)->for($book)->create(['status' => BookRentStatus::Active]);

        $due = $this->dueSoon();
        $cases = [
            ['GET', '/api/v1/rentals', []],
            ['POST', '/api/v1/rentals', ['book_id' => $book->id, 'due_date' => $due]],
            ['GET', '/api/v1/rentals/'.$rent->id, []],
            ['PATCH', '/api/v1/rentals/'.$rent->id.'/extend', ['due_date' => $due]],
            ['GET', '/api/v1/rentals/'.$rent->id.'/reading-progress', []],
            ['PATCH', '/api/v1/rentals/'.$rent->id.'/reading-progress', ['reading_progress' => 10]],
            ['POST', '/api/v1/rentals/'.$rent->id.'/finish', []],
        ];

        foreach ($cases as [$method, $uri, $payload]) {
            $response = match ($method) {
                'GET' => $this->getJson($uri),
                'POST' => $this->postJson($uri, $payload),
                'PATCH' => $this->patchJson($uri, $payload),
                default => throw new \InvalidArgumentException('Unsupported method: '.$method),
            };

            $response->assertUnauthorized();
        }
    }
}
