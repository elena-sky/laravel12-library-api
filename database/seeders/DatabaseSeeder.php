<?php

namespace Database\Seeders;

use App\Enums\BookRentStatus;
use App\Models\Book;
use App\Models\BookRent;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demoUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Book::factory()->createMany([
            [
                'title' => 'Demo: Library API Guide',
                'author' => 'Seed Catalog',
                'genre' => 'Classic',
                'description' => 'Fixed row for manual catalog checks.',
                'total_copies' => 5,
                'available_copies' => 5,
            ],
            [
                'title' => 'Demo: Sci-Fi Anthology',
                'author' => 'Seed Catalog',
                'genre' => 'Sci-Fi',
                'description' => null,
                'total_copies' => 2,
                'available_copies' => 2,
            ],
        ]);

        $bookWithActiveRent = Book::query()->create([
            'title' => 'Demo: Active rental copy',
            'author' => 'Seed Catalog',
            'genre' => 'Fiction',
            'description' => 'One copy is on active rent (available_copies matches).',
            'total_copies' => 3,
            'available_copies' => 2,
        ]);

        BookRent::query()->create([
            'user_id' => $demoUser->id,
            'book_id' => $bookWithActiveRent->id,
            'status' => BookRentStatus::Active,
            'rented_at' => now()->subDays(3),
            'due_date' => now()->addDays(11),
            'returned_at' => null,
            'reading_progress' => 15,
            'extended_count' => 0,
        ]);

        $bookWithFinishedRent = Book::query()->create([
            'title' => 'Demo: Finished rental history',
            'author' => 'Seed Catalog',
            'genre' => 'Biography',
            'description' => 'Finished rent in seed; all copies available.',
            'total_copies' => 2,
            'available_copies' => 2,
        ]);

        BookRent::query()->create([
            'user_id' => $demoUser->id,
            'book_id' => $bookWithFinishedRent->id,
            'status' => BookRentStatus::Finished,
            'rented_at' => now()->subDays(20),
            'due_date' => now()->subDays(5),
            'returned_at' => now()->subDays(6),
            'reading_progress' => 100,
            'extended_count' => 0,
        ]);

        Book::factory()->count(5)->create();
    }
}
