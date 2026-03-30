<?php

namespace App\Actions\BookRent;

use App\Enums\BookRentStatus;
use App\Exceptions\ResourceConflictException;
use App\Models\Book;
use App\Models\BookRent;
use App\Models\User;
use App\Support\BookListCache;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

final class RentBookAction
{
    public function __construct(
        private readonly BookListCache $bookListCache,
    ) {}

    /**
     * @throws ResourceConflictException
     */
    public function execute(User $user, int $bookId, CarbonInterface $dueDate): BookRent
    {
        $rent = DB::transaction(function () use ($user, $bookId, $dueDate): BookRent {
            /** @var Book $locked */
            $locked = Book::query()->whereKey($bookId)->lockForUpdate()->firstOrFail();

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

        $this->bookListCache->bumpVersion();

        return $rent;
    }
}
