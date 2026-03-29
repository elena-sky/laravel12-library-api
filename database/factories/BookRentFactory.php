<?php

namespace Database\Factories;

use App\Enums\BookRentStatus;
use App\Models\Book;
use App\Models\BookRent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookRent>
 */
class BookRentFactory extends Factory
{
    protected $model = BookRent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rentedAt = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'status' => BookRentStatus::Active,
            'rented_at' => $rentedAt,
            'due_date' => (clone $rentedAt)->modify('+14 days'),
            'returned_at' => null,
            'reading_progress' => fake()->numberBetween(0, 100),
            'extended_count' => 0,
        ];
    }

    public function finished(): static
    {
        return $this->state(function (array $attributes): array {
            $rentedAt = $attributes['rented_at'] ?? now()->subDays(10);

            return [
                'status' => BookRentStatus::Finished,
                'returned_at' => now(),
            ];
        });
    }
}
