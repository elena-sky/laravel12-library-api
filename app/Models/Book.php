<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Catalog item with copy counts; availability for rent is available_copies > 0.
 *
 * @property int $id
 * @property string $title
 * @property string $author
 * @property string $genre
 * @property string|null $description
 * @property int $total_copies
 * @property int $available_copies
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, BookRent> $bookRents
 */
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'title',
        'author',
        'genre',
        'description',
        'total_copies',
        'available_copies',
    ];

    /**
     * @return HasMany<BookRent, $this>
     */
    public function bookRents(): HasMany
    {
        return $this->hasMany(BookRent::class);
    }
}
