<?php

namespace App\Models;

use App\Enums\BookRentStatus;
use Database\Factories\BookRentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One rental transaction between a user and a book copy.
 *
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property BookRentStatus $status
 * @property int $reading_progress
 * @property int $extended_count
 * @property Carbon $rented_at
 * @property Carbon $due_date
 * @property Carbon|null $returned_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Book $book
 */
class BookRent extends Model
{
    /** @use HasFactory<BookRentFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'rented_at',
        'due_date',
        'returned_at',
        'reading_progress',
        'extended_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BookRentStatus::class,
            'rented_at' => 'datetime',
            'due_date' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Book, $this>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function isActive(): bool
    {
        return $this->status === BookRentStatus::Active;
    }
}
