<?php

namespace App\Actions\BookRent;

use App\Enums\BookRentStatus;
use App\Exceptions\ResourceConflictException;
use App\Models\Book;
use App\Models\BookRent;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

final class RentBookAction
{
    /**
     * @throws ResourceConflictException
     */
    public function execute(User $user, Book $book, CarbonInterface $dueDate): BookRent
    {
        return DB::transaction(function () use ($user, $book, $dueDate): BookRent {
            /** @var Book $locked */
            $locked = Book::query()->whereKey($book->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->available_copies < 1) {
                throw new ResourceConflictException('Book is not available for rent');
            }

            $locked->available_copies--;
            $locked->save();

            return BookRent::query()->create([
                'user_id' => $user->id,
                'book_id' => $locked->id,
                'status' => BookRentStatus::Active,
                'rented_at' => now(),
                'due_date' => $dueDate,
                'returned_at' => null,
                'reading_progress' => 0,
                'extended_count' => 0,
            ]);
        });
    }
}
