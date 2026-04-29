<?php

namespace App\Http\Resources;

use App\Models\BookRent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BookRent
 */
class BookRentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'book_id' => $this->book_id,
            'status' => $this->status->value,
            'rented_at' => $this->rented_at->toIso8601String(),
            'due_date' => $this->due_date->toIso8601String(),
            'returned_at' => $this->returned_at?->toIso8601String(),
            'reading_progress' => $this->reading_progress,
            'extended_count' => $this->extended_count,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'book' => BookResource::make($this->whenLoaded('book')),
        ];
    }
}
