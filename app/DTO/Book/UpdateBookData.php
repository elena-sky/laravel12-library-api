<?php

namespace App\DTO\Book;

use Illuminate\Database\Eloquent\Model;

final readonly class UpdateBookData
{
    /**
     * @param  array<string, mixed>  $attributes  Only keys present in the request (validated subset).
     */
    private function __construct(
        private array $attributes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self($validated);
    }

    /**
     * Attributes for {@see Model::fill()}.
     *
     * @return array<string, mixed>
     */
    public function toFillArray(): array
    {
        return $this->attributes;
    }
}
